<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\UserNotProException;
use app\core\models\Account;
use app\core\models\Record;
use app\core\services\AccountService;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Yii;
use yii\web\UnauthorizedHttpException;
use yiier\helpers\Setup;

/**
 *
 * @property-read array|string[] $paramsDate
 * @property-read array $accountIds
 */
class InvestmentController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';

    /**
     * @return array
     * @throws \Exception
     */
    public function actionOverview(): array
    {
        $this->checkAccess('');
        $items = [];
        $paramsDate = $this->getParamsDate();
        $accountIds = $this->getAccountIds();
        $balanceCentSum = Account::find()->where(['id' => $accountIds])->sum('balance_cent');
        $items['total_balance'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        // 本金 = 初始金额+转入金额-转出金额
        // 收益 = 余额调整收入 - 余额调整支出

        foreach ($accountIds as $id) {
            $first = Record::find()->where(['account_id' => $id])->orderBy(['date' => SORT_ASC])->one();

            foreach (DirectionType::names() as $key => $value) {
                $newQuery = Record::find()->andWhere(['!=', 'id', $first->id])
                    ->andWhere(['!=', 'transaction_type', TransactionType::ADJUST])
                    ->andWhere(['direction' => $key, 'exclude_from_stats' => false]);
                $sum = $newQuery->sum('amount_cent');

                $items[$value][$id] = $sum ? (float)Setup::toYuan($sum + $first->amount_cent) : 0;
                $items["date_{$value}"][$id] = $items[$value][$id];

                if (count($paramsDate)) {
                    $newQuery->andWhere(['between', 'date', $paramsDate[0], $paramsDate[1]]);
                    $dateSum = $newQuery->sum('amount_cent');
                    $items["date_{$value}"][$id] = $dateSum ? (float)Setup::toYuan($dateSum + $first->amount_cent) : 0;
                }
            }

            bcscale(2);
            $items['count'] = count($accountIds);

            $items['total_income'] = bcsub(
                array_sum($items[DirectionType::getName(DirectionType::INCOME)]),
                array_sum($items[DirectionType::getName(DirectionType::EXPENSE)])
            );
            $items['total_income'] = bcsub(
                array_sum($items['date_' . DirectionType::getName(DirectionType::INCOME)]),
                array_sum($items['date_' . DirectionType::getName(DirectionType::EXPENSE)])
            );
            // 累计投入
            $items['init_balance_sum'] = bcsub($items['total_balance'], $items['total_income']);
            unset(
                $items[DirectionType::getName(DirectionType::INCOME)],
                $items[DirectionType::getName(DirectionType::EXPENSE)],
                $items['date_' . DirectionType::getName(DirectionType::INCOME)],
                $items['date_' . DirectionType::getName(DirectionType::EXPENSE)]
            );
//            $sum = $query->sum('amount_cent');
//
//            $items['income'][$id] = $sum ? (float)Setup::toYuan($sum) : 0;
//
//            $query = Record::find()->where($conditions)->andWhere(['direction' => DirectionType::EXPENSE]);
//            $sum = $query->sum('amount_cent');
//            $items['expense'][$id] = $sum ? (float)Setup::toYuan($sum) : 0;
        }
//        bcscale(2);
//        $items['count'] = count($items['expense']);
//        // 累计投入
//        $items['total_income'] = array_sum($items['income']);
//        // 时间投入
//        $items['date_income'] = array_sum($items['date_income']);
//
//
//        unset($items['expense'], $items['income']);

        return $items;
    }


    public function actionStatistics(): array
    {
        $this->checkAccess('');
        $items = $conditions = [];
        $paramsDate = $this->getParamsDate();
        foreach ($this->getAccountIds() as $id) {
            if (count($paramsDate)) {
                $conditions = ['between', 'date', $paramsDate[0], $paramsDate[1]];
            }
            $items[$id]['income_sum'] = AccountService::getCalculateIncomeSumCent($id, $conditions);
        }
        return $items;
    }


    protected function getAccountIds(): array
    {
        $baseConditions = [
            'user_id' => Yii::$app->user->id,
            'type' => AccountType::INVESTMENT_ACCOUNT,
            'exclude_from_stats' => false
        ];
        $balanceCentSum = Account::find()->where($baseConditions)->sum('balance_cent');
        $items['balance_sum'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;


        return Account::find()->where($baseConditions)->column();
    }

    public function getParamsDate(): array
    {
        if (($date = explode('~', data_get(Yii::$app->request->queryParams, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            return [$start, $end];
        }
        return [];
    }


    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws UnauthorizedHttpException
     * @throws UserNotProException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (!Yii::$app->user->id) {
            throw new UnauthorizedHttpException('Your request was made with invalid credentials.');
        }
        if (!UserProService::isPro()) {
            throw new UserNotProException();
        }
    }
}
