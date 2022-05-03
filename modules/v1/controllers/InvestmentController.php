<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\exceptions\UserNotProException;
use app\core\models\Account;
use app\core\models\Record;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Yii;
use yii\web\UnauthorizedHttpException;
use yiier\helpers\Setup;

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
        $baseConditions = [
            'user_id' => UserService::getCurrentMemberIds(),
            'type' => AccountType::INVESTMENT_ACCOUNT,
            'exclude_from_stats' => false,
        ];
        $balanceCentSum = Account::find()->where($baseConditions)->sum('balance_cent');
        $items['balance_sum'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        if (($date = explode('~', data_get(Yii::$app->request->queryParams, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            $paramsDate = [$start, $end];
        }
        $items['income'] = [];
        $items['expense'] = [];
        foreach (Account::find()->where($baseConditions)->column() as $id) {
            $conditions = ['account_id' => $id, 'transaction_type' => TransactionType::ADJUST];

            $query = Record::find()->where($conditions)->andWhere(['direction' => DirectionType::INCOME]);
            if (isset($paramsDate)) {
                $query->andWhere(['between', 'date', $paramsDate[0], $paramsDate[1]]);
            }
            $sum = $query->sum('amount_cent');
            $first = $query->select('amount_cent')->orderBy(['date' => SORT_ASC])->limit(1)->scalar();

            $items['income'][$id] = $sum ? (float) Setup::toYuan($sum - $first) : 0;

            $query = Record::find()->where($conditions)->andWhere(['direction' => DirectionType::EXPENSE]);
            $sum = $query->sum('amount_cent');
            $items['expense'][$id] = $sum ? (float) Setup::toYuan($sum) : 0;
        }
        bcscale(2);
        $items['count'] = count($items['expense']);
        $items['income_sum'] = bcsub(array_sum($items['income']), array_sum($items['expense']));
        $items['init_balance_sum'] = bcsub($items['balance_sum'], $items['income_sum']);
        unset($items['expense'], $items['income']);

        return $items;
    }


    /**
     * @param  string  $action
     * @param  null  $model
     * @param  array  $params
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
