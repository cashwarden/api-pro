<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\models\Account;
use app\core\models\Budget;
use app\core\models\BudgetConfig;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\LedgerMember;
use app\core\models\Record;
use app\core\models\Recurrence;
use app\core\models\Rule;
use app\core\models\Tag;
use app\core\models\Transaction;
use app\core\models\WishList;
use app\core\requests\LedgerInvitingMember;
use app\core\types\ColorType;
use app\core\types\LedgerMemberRule;
use app\core\types\LedgerMemberStatus;
use app\core\types\LedgerType;
use app\core\types\TransactionType;
use Yii;
use yii\db\Exception as DBException;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yiier\graylog\Log;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class LedgerService
{
    /**
     * @param int $ledgerId
     * @param int $permission
     * @throws ForbiddenHttpException
     */
    public static function checkAccess(int $ledgerId, $permission = RuleControlHelper::VIEW)
    {
        $userId = Yii::$app->user->id;
        $userPermission = LedgerMember::find()
            ->select('permission')
            ->where(['user_id' => $userId, 'ledger_id' => $ledgerId])
            ->scalar();
        if (!RuleControlHelper::can($userPermission, $permission)) {
            throw new ForbiddenHttpException(
                Yii::t('app', 'You do not have permission to operate.')
            );
        }
    }

    public static function checkAccessOnType(int $ledgerId, int $userId, string $action)
    {
        $ledger = Ledger::find()->where(['id' => $ledgerId])->asArray()->one();
        if ((LedgerType::SHARE != $ledger['type']) && (Yii::$app->user->id != $userId)) {
            throw new ForbiddenHttpException(
                Yii::t('app', 'You can only ' . $action . ' data that you\'ve created.')
            );
        }
    }

    /**
     * @param int $ledgerId
     * @return array
     */
    public static function getLedgerMemberUserIds(int $ledgerId): array
    {
        $userIds = LedgerMember::find()
            ->select('user_id')
            ->where(['ledger_id' => $ledgerId, 'status' => [LedgerMemberStatus::NORMAL, LedgerMemberStatus::ARCHIVED]])
            ->column();
        return array_map('intval', $userIds);
    }

    /**
     * @param int $ledgerId
     * @return array
     */
    public static function getLedgerMemberUserIdsByType(int $ledgerId): array
    {
        $ledger = Ledger::find()->where(['id' => $ledgerId])->asArray()->one();
        if (LedgerType::SHARE == $ledger['type']) {
            $userIds = LedgerMember::find()
                ->select('user_id')
                ->where([
                    'ledger_id' => $ledgerId,
                    'status' => [LedgerMemberStatus::NORMAL, LedgerMemberStatus::ARCHIVED]
                ])
                ->column();
            return array_map('intval', $userIds);
        }
        return [(int)Yii::$app->user->id];
    }

    /**
     * @param int $userId
     * @return array
     */
    public static function getLedgerIds(int $userId = 0): array
    {
        $userId = $userId ?: Yii::$app->user->id;
        $ledgerIds = LedgerMember::find()
            ->select('ledger_id')
            ->where(['user_id' => $userId, 'status' => [LedgerMemberStatus::NORMAL, LedgerMemberStatus::ARCHIVED]])
            ->column();
        return array_map('intval', $ledgerIds);
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
    public static function createLedgerAfter(Ledger $ledger): bool
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
                $rows[$key]['ledger_id'] = $ledger->id;
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

    public static function afterDelete(int $ledgerId)
    {
        LedgerMember::deleteAll(['ledger_id' => $ledgerId]);
        Category::deleteAll(['ledger_id' => $ledgerId]);
        $budgetConfigIds = BudgetConfig::find()->where(['ledger_id' => $ledgerId])->column();
        Budget::deleteAll(['budget_config_id' => $budgetConfigIds]);
        BudgetConfig::deleteAll(['ledger_id' => $ledgerId]);
        Record::deleteAll(['ledger_id' => $ledgerId]);
        Rule::deleteAll(['ledger_id' => $ledgerId]);
        Tag::deleteAll(['ledger_id' => $ledgerId]);
        WishList::deleteAll(['ledger_id' => $ledgerId]);
        $transactionIds = Transaction::find()->where(['ledger_id' => $ledgerId])->column();
        Recurrence::deleteAll(['transaction_id' => $transactionIds]);
        Transaction::deleteAll(['ledger_id' => $ledgerId]);
    }

    /**
     * @param LedgerInvitingMember $model
     * @return bool
     * @throws InternalException
     */
    public function invitingMember(LedgerInvitingMember $model): bool
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
    public function getLedgersCategories(): array
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


    /**
     * @param string $token
     * @return Ledger
     * @throws NotFoundHttpException
     */
    public function getLedgerByToken(string $token): Ledger
    {
        $id = Yii::$app->hashids->decode($token);
        if (!$model = Ledger::find()->where(['id' => $id, 'type' => [LedgerType::SHARE, LedgerType::AA]])->one()) {
            throw new NotFoundHttpException();
        }

        return $model;
    }

    /**
     * @param string $token
     * @return bool
     * @throws DBException
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     */
    public function joinLedgerByToken(string $token): bool
    {
        $userId = Yii::$app->user->id;
        $ledger = $this->getLedgerByToken($token);
        $ledgerMember = LedgerMember::find()->where(['ledger_id' => $ledger->id, 'user_id' => $userId])->one();
        if ($ledgerMember) {
            throw new InvalidArgumentException(
                Yii::t('app', 'You have successfully joined this account, please do not repeat the process.')
            );
        }
        $model = new LedgerMember();
        $model->ledger_id = $ledger->id;
        $model->user_id = $userId;
        $model->rule = LedgerMemberRule::getName(LedgerMemberRule::VIEWER);
        $model->status = LedgerMemberStatus::getName(LedgerMemberStatus::NORMAL);
        if (!$model->save()) {
            throw new \yii\db\Exception(Setup::errorMessage($model->firstErrors));
        }
        return true;
    }
}
