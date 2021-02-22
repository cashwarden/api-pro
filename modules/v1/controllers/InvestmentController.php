<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\UserNotProException;
use app\core\models\Account;
use app\core\models\Record;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Yii;
use yiier\helpers\Setup;

class InvestmentController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws UserNotProException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (!UserProService::isPro()) {
            throw new UserNotProException();
        }
        return parent::beforeAction($action);
    }

    /**
     * @return array
     */
    public function actionOverview(): array
    {
        $baseConditions = [
            'user_id' => Yii::$app->user->id,
            'type' => AccountType::INVESTMENT_ACCOUNT,
            'exclude_from_stats' => false
        ];
        $balanceCentSum = Account::find()->where($baseConditions)->sum('balance_cent');
        $items['balance_sum'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        foreach (Account::find()->where($baseConditions)->column() as $id) {
            $conditions = ['account_id' => $id, 'transaction_type' => TransactionType::ADJUST];
            $query = Record::find()->where($conditions)->andWhere(['direction' => DirectionType::INCOME]);
            $sum = $query->sum('amount_cent');
            $first = $query->select('amount_cent')->orderBy(['date' => SORT_ASC])->limit(1)->scalar();

            $items['income'][$id] = $sum ? (float)Setup::toYuan($sum - $first) : 0;

            $query = Record::find()->where($conditions)->andWhere(['direction' => DirectionType::EXPENSE]);
            $sum = $query->sum('amount_cent');
            $items['expense'][$id] = $sum ? (float)Setup::toYuan($sum) : 0;
        }
        bcscale(2);
        $items['count'] = count($items['expense']);
        $items['income_sum'] = bcsub(array_sum($items['income']), array_sum($items['expense']));
        $items['init_balance_sum'] = bcsub($items['balance_sum'], $items['income_sum']);
        unset($items['expense'], $items['income']);

        return $items;
    }
}
