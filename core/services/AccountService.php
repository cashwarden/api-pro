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

use app\core\exceptions\InternalException;
use app\core\helpers\DateHelper;
use app\core\models\Account;
use app\core\models\Record;
use app\core\models\Recurrence;
use app\core\models\Rule;
use app\core\models\Transaction;
use app\core\types\AccountStatus;
use app\core\types\DirectionType;
use app\core\types\ReimbursementStatus;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yiier\helpers\Setup;

class AccountService
{
    public static function afterDelete(Account $account)
    {
        $baseConditions = ['user_id' => UserService::getCurrentMemberIds()];
        Record::deleteAll($baseConditions + ['account_id' => $account->id]);

        $transactionIds = Transaction::find()
            ->where([
                'and',
                $baseConditions,
                ['or', ['from_account_id' => $account->id], ['to_account_id' => $account->id]],
            ])
            ->column();
        Transaction::deleteAll($baseConditions + ['id' => $transactionIds]);
        Recurrence::deleteAll($baseConditions + ['transaction_id' => $transactionIds]);
        TransactionService::deleteXunSearch($transactionIds);

        Rule::deleteAll([
            'and',
            $baseConditions,
            ['or', ['then_from_account_id' => $account->id], ['then_to_account_id' => $account->id]],
        ]);
    }

    /**
     * @param  Account  $account
     * @return Account
     * @throws InternalException
     */
    public function createUpdate(Account $account): Account
    {
        try {
            $account->user_id = $account->user_id ?: Yii::$app->user->id;
            if (!$account->save()) {
                throw new \yii\db\Exception(Setup::errorMessage($account->firstErrors));
            }
        } catch (Exception $e) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $account->attributes, $account->errors, (string) $e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
        return Account::findOne($account->id);
    }


    /**
     * @param  int  $id
     * @return Account|ActiveRecord|null
     */
    public static function findOne(int $id)
    {
        return Account::find()->where(['id' => $id])->one();
    }

    public static function getDefaultAccount(): array
    {
        $userIds = UserService::getCurrentMemberIds();
        return Account::find()
            ->where(['user_id' => $userIds])
            ->orderBy(['default' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->one();
    }

    /**
     * @param  int  $accountId
     * @param  array  $userIds
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function updateAccountBalance(int $accountId, array $userIds): bool
    {
        if (!$model = Account::find()->where(['id' => $accountId, 'user_id' => $userIds])->one()) {
            throw new \yii\db\Exception(Yii::t('app', 'Not found account.'));
        }
        $model->load($model->toArray(), '');
        $model->currency_balance = Setup::toYuan(self::getCalculateCurrencyBalanceCent($accountId));
        if (!$model->save()) {
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $model->attributes, $model->errors],
                __FUNCTION__
            );
            throw new \yii\db\Exception('update account failure ' . Setup::errorMessage($model->firstErrors));
        }
        return true;
    }


    /**
     * @param  int  $accountId
     * @return int
     */
    public static function getCalculateCurrencyBalanceCent(int $accountId): int
    {
        $in = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::INCOME,
            'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
        ])->sum('currency_amount_cent');

        $out = Record::find()->where([
            'account_id' => $accountId,
            'direction' => DirectionType::EXPENSE,
            'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
        ])->sum('currency_amount_cent');

        return ($in - $out) ?: 0;
    }

    /**
     * @param  int  $accountId
     * @return int
     * @throws Exception
     */
    public static function getCalculateIncomeSumCent(int $accountId): int
    {
        $first = Record::find()
            ->where([
                'account_id' => $accountId,
                'transaction_type' => TransactionType::ADJUST,
                'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
            ])
            ->orderBy(['date' => SORT_ASC])
            ->limit(1)
            ->asArray()
            ->one();

        $in = Record::find()
            ->where([
                'account_id' => $accountId,
                'transaction_type' => TransactionType::ADJUST,
                'direction' => DirectionType::INCOME,
                'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
            ])
            ->andFilterWhere(['!=', 'id', data_get($first, 'id')])
            ->sum('currency_amount_cent');

        $out = Record::find()
            ->where([
                'account_id' => $accountId,
                'transaction_type' => TransactionType::ADJUST,
                'direction' => DirectionType::EXPENSE,
                'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
            ])
            ->andFilterWhere(['!=', 'id', data_get($first, 'id')])
            ->sum('currency_amount_cent');

        return ($in - $out) ?: 0;
    }

    /**
     * @return array
     */
    public static function getCurrentMap(): array
    {
        $accounts = Account::find()->where(['user_id' => UserService::getCurrentMemberIds()])->asArray()->all();
        return ArrayHelper::map($accounts, 'id', 'name');
    }

    /**
     * @param  Account  $model
     * @param  string  $endDate
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function balancesTrend(Account $model, string $endDate): array
    {
        $currentBalanceCent = $model->balance_cent;
        $data = Record::find()
            ->where(['account_id' => $model->id, 'exclude_from_stats' => (int) false])
            ->andWhere(['>=', 'date', $endDate])
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
            ->asArray()
            ->all();
        $items = [];
        $rows = [];
        foreach ($data as $datum) {
            $date = DateHelper::toDate($datum['date']);
            $amountCent = $datum['direction'] == DirectionType::INCOME ?
                -$datum['amount_cent'] : $datum['amount_cent'];
            if (!isset($rows[$date]['amount_cent'])) {
                $rows[$date]['amount_cent'] = 0;
            }
            $rows[$date]['amount_cent'] += $amountCent;
        }
        $dares = DateHelper::getMonthRange($endDate);
        foreach ($dares as $date) {
            $afterBalanceCent = $afterBalanceCent ?? $currentBalanceCent;
            if (!isset($items[$date])) {
                $items[$date] = ['date' => $date, 'after_balance' => (float) Setup::toYuan($afterBalanceCent)];
            }
            $afterBalanceCent = $afterBalanceCent + data_get($rows, "{$date}.amount_cent", 0);
        }
        return array_reverse(array_values($items));
    }


    /**
     * @param  string  $desc
     * @param  int|null  $excludeAccountId
     * @return int
     */
    public function getAccountIdByDesc(string $desc, ?int $excludeAccountId = null): int
    {
        /** @var Account $model */
        foreach (self::getHasKeywordAccounts() as $model) {
            if (\app\core\helpers\ArrayHelper::strPosArr($desc, explode(',', $model->keywords)) !== false) {
                if ($model->id != $excludeAccountId) {
                    return $model->id;
                }
            }
        }
        return 0;
    }

    /**
     * @return array|ActiveRecord[]
     */
    public static function getHasKeywordAccounts(): array
    {
        return Account::find()
            ->where(['user_id' => UserService::getCurrentMemberIds(), 'status' => AccountStatus::ACTIVE])
            ->andWhere(['<>', 'keywords', ''])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])
            ->all();
    }
}
