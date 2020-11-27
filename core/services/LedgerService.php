<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\LedgerMember;
use app\core\requests\LedgerInvitingMember;
use app\core\types\ColorType;
use app\core\types\LedgerMemberRule;
use app\core\types\LedgerMemberStatus;
use app\core\types\TransactionType;
use Yii;
use yii\db\Exception as DBException;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yiier\graylog\Log;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class LedgerService
{
    /**
     * @param int $ledgerId
     * @throws ForbiddenHttpException
     */
    public static function checkAccess(int $ledgerId)
    {
        $userId = Yii::$app->user->id;
        $rule = LedgerMember::find()
            ->select('permission')
            ->where(['user_id' => $userId, 'ledger_id' => $ledgerId])
            ->scalar();
        if (!$rule) {
            throw new ForbiddenHttpException(
                Yii::t('app', 'You can only view data that you\'ve created.')
            );
        }
    }

    /**
     * @param int $ledgerId
     * @return array
     */
    public static function getLedgerMemberUserIds(int $ledgerId)
    {
        $userIds = LedgerMember::find()
            ->select('user_id')
            ->where(['ledger_id' => $ledgerId, 'status' => [LedgerMemberStatus::NORMAL, LedgerMemberStatus::ARCHIVED]])
            ->column();
        return array_map('intval', $userIds);
    }

    /**
     * @param int $userId
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getDefaultLedger(int $userId)
    {
        return Ledger::find()
            ->where(['user_id' => $userId])
            ->orderBy(['default' => SORT_DESC, 'id' => SORT_ASC])
            ->one();
    }

    /**
     * @param Ledger $ledger
     * @return bool
     * @throws InternalException
     */
    public static function createLedgerAfter(Ledger $ledger)
    {
        try {
            $model = new LedgerMember();
            $model->ledger_id = $ledger->id;
            $model->user_id = $ledger->user_id;
            $model->rule = LedgerMemberRule::getName(LedgerMemberRule::OWNER);
            $model->status = LedgerMemberStatus::getName(LedgerMemberStatus::NORMAL);
            if (!$model->save()) {
                throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
            }
            $items = [
                [
                    'name' => Yii::t('app', 'Other expenses'),
                    'color' => ColorType::GEEK_BLUE,
                    'icon_name' => 'expenses',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Account::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Other income'),
                    'color' => ColorType::MAGENTA,
                    'icon_name' => 'income',
                    'transaction_type' => TransactionType::INCOME,
                    'default' => Category::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Transfer'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'transfer',
                    'transaction_type' => TransactionType::TRANSFER,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Adjust Balance'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'adjust',
                    'transaction_type' => TransactionType::ADJUST,
                    'default' => Category::NOT_DEFAULT,
                ],
            ];
            $time = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($items as $key => $value) {
                $rows[$key] = $value;
                $rows[$key]['user_id'] = $ledger->user_id;
                $rows[$key]['created_at'] = $time;
                $rows[$key]['updated_at'] = $time;
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
        } catch (\Exception $e) {
            Log::error('创建账本失败', [$ledger->attributes, (string)$e]);
            throw new InternalException($e->getMessage());
        }
        return true;
    }

    /**
     * @param LedgerInvitingMember $model
     * @return bool
     * @throws InternalException
     */
    public function invitingMember(LedgerInvitingMember $model)
    {
        try {
            $ledgerMember = new LedgerMember();
            $ledgerMember->load($model->attributes, '');
            $ledgerMember->status = LedgerMemberStatus::getName(LedgerMemberStatus::WAITING);
            if (!$ledgerMember->save()) {
                throw new \yii\db\Exception(Setup::errorMessage($ledgerMember->firstErrors));
            }
        } catch (\Exception $e) {
            Log::error('邀请用户到账本失败', [['ledger_id' => $model->attributes], (string)$e]);
            throw new InternalException($e->getMessage());
        }
        return true;
    }

    /**
     * @return array
     */
    public function getLedgersCategories()
    {
        $rows = [];
        /** @var Ledger[] $items */
        $items = Ledger::find()->where(['user_id' => Yii::$app->user->id])->all();
        foreach ($items as $item) {
            $array = ArrayHelper::toArray($item->categories);
            $categories = ArrayHelper::index($array, null, 'transaction_type');
            $row = [
                'id' => $item->id,
                'name' => $item->name,
                'categories' => $categories,
            ];

            array_push($rows, $row);
        }
        return $rows;
    }
}
