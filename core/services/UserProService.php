<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\exceptions\PayException;
use app\core\exceptions\UserNotProException;
use app\core\models\Account;
use app\core\models\BudgetConfig;
use app\core\models\Currency;
use app\core\models\Ledger;
use app\core\models\LedgerMember;
use app\core\models\Recurrence;
use app\core\models\Rule;
use app\core\models\UserProRecord;
use app\core\models\WishList;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\LedgerType;
use app\core\types\UserProRecordSource;
use app\core\types\UserProRecordStatus;
use Carbon\Carbon;
use Exception;
use Yii;
use yii\db\Exception as DBException;
use yii\helpers\ArrayHelper;
use yiier\graylog\Log;
use yiier\helpers\Setup;

class UserProService
{
    use ServiceTrait;

    public static function isPro(int $userId = 0): bool
    {
        $userId = $userId ?: Yii::$app->user->id;
        // todo 缓存
        return UserProRecord::find()
            ->where(['user_id' => $userId, 'status' => UserProRecordStatus::PAID])
            ->andWhere(['>=', 'ended_at', Carbon::now()->toDateTimeString()])
            ->orderBy(['ended_at' => SORT_DESC])
            ->exists();
    }


    /**
     * @param string $modelClass
     * @param string $action
     * @param null $model
     * @return bool
     * @throws UserNotProException|\app\core\exceptions\InvalidArgumentException
     */
    public static function checkAccess(string $modelClass, string $action, $model = null): bool
    {
        if (self::isPro()) {
            return true;
        }
        if (!in_array($action, ['create', 'update'])) {
            return true;
        }
        $baseConditions = ['user_id' => Yii::$app->user->id];

        switch ($modelClass) {
            case Account::class:
                $count = Account::find()->where($baseConditions)->count('id');
                if ($action == 'create' && $count >= params('userAccountTotal')) {
                    throw new UserNotProException();
                }
                if (data_get($model, 'type') == AccountType::getName(AccountType::INVESTMENT_ACCOUNT)) {
                    throw new UserNotProException();
                }
                break;
            case Ledger::class:
                if (Ledger::find()->where($baseConditions)->count('id') >= params('userLedgerTotal')) {
                    throw new UserNotProException();
                }
                if (in_array(data_get($model, 'type'), [LedgerType::SHARE, LedgerType::AA])) {
                    throw new UserNotProException();
                }
                break;
            case Recurrence::class:
                if (Recurrence::find()->where($baseConditions)->count('id') >= params('userRecurrenceTotal')) {
                    throw new UserNotProException();
                }
                break;
            case Rule::class:
                $count = Rule::find()->where($baseConditions)->count('id');
                if ($action == 'create' && $count >= params('userRuleTotal')) {
                    throw new UserNotProException();
                }
                break;
            case BudgetConfig::class:
            case LedgerMember::class:
            case Currency::class:
            case WishList::class:
                throw new UserNotProException();
        }
        return true;
    }

    /**
     * @return UserProRecord
     * @throws DBException
     */
    public function upgradeToPro(): UserProRecord
    {
        $userId = Yii::$app->user->id;
        $model = UserProRecord::find()
            ->where(['user_id' => $userId, 'status' => UserProRecordStatus::TO_BE_PAID])
            ->andWhere(['<=', 'created_at', Carbon::now()->addMinutes(20)->toDateTimeString()])
            ->one();
        if (!$model) {
            $model = new UserProRecord();
            $model->out_sn = UserProRecord::makeOrderNo();
            $model->status = UserProRecordStatus::TO_BE_PAID;
        }
        $model->user_id = $userId;
        $model->source = UserProRecordSource::BUY;
        $model->amount_cent = params('proUserPriceCent');
        $model->ended_at = Carbon::now()->toDateTimeString();
        if (!$model->save()) {
            Log::error('升级会员失败', [$model->attributes, $model->errors]);
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param int $userId
     * @param string $endedAt
     * @return UserProRecord
     * @throws DBException
     */
    public static function upgradeToProBySystem(int $userId, string $endedAt): UserProRecord
    {
        $model = new UserProRecord();
        $model->out_sn = UserProRecord::makeOrderNo();
        $model->status = UserProRecordStatus::PAID;
        $model->user_id = $userId;
        $model->source = UserProRecordSource::SYSTEM;
        $model->amount_cent = 0;
        $model->ended_at = $endedAt;
        if (!$model->save()) {
            Log::error('系统赠送会员失败', [$model->attributes, $model->errors]);
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    public static function getUserProLastEndedAt(int $userId)
    {
        $now = Carbon::now()->toDateTimeString();
        $model = UserProRecord::find()
            ->where(['user_id' => $userId, 'status' => UserProRecordStatus::PAID])
            ->andWhere(['>=', 'ended_at', $now])
            ->one();
        if ($model) {
            return $model->ended_at;
        }
        return $now;
    }

    /**
     * @param string $outSn
     * @param array $conditions
     * @param array $post
     * @return bool
     * @throws PayException|Exception
     */
    public function paySuccess(string $outSn, array $conditions, $post = []): bool
    {
        /** @var UserProRecord $record */
        $record = UserProRecord::find()->where(['out_sn' => $outSn])->andWhere($conditions)->limit(1)->one();
        if (!$record) {
            throw new Exception('未找到订单');
        }

        $key = ArrayHelper::getValue($post, 'total_amount');
        if ($record->amount_cent != Setup::toFen($key)) {
            throw new PayException('订单金额有误');
        }

        $record->remark = json_encode($post);
        $record->ended_at = Carbon::parse(self::getUserProLastEndedAt($record->user_id))->addMonths(12)->endOfDay();
        $record->status = UserProRecordStatus::PAID;
        if (!$record->save()) {
            Log::error('支付更新失败', [$record->attributes, $record->errors]);
            throw new PayException('支付通知失败');
        }
        return true;
    }

    public function getUserProRecord(string $outSn)
    {
        return UserProRecord::find()
            ->where(['user_id' => Yii::$app->user->id, 'out_sn' => $outSn])
            ->one();
    }
}
