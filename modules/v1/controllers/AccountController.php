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

use app\core\helpers\DateHelper;
use app\core\models\Account;
use app\core\services\AccountService;
use app\core\services\UserService;
use app\core\traits\ServiceTrait;
use app\core\types\AccountStatus;
use app\core\types\AccountType;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

/**
 * Account controller for the `v1` module.
 */
class AccountController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Account::class;
    public array $noAuthActions = [];
    public array $defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];
    public array $stringToIntAttributes = ['type' => AccountType::class, 'status' => AccountStatus::class];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['create']);
        return $actions;
    }

    /**
     * @return Account
     * @throws Exception
     */
    public function actionCreate(): Account
    {
        $params = Yii::$app->request->bodyParams;
        $model = new Account();
        $model->user_id = 0;
        if (data_get($params, 'type') == AccountType::CREDIT_CARD) {
            $model->setScenario(AccountType::CREDIT_CARD);
        }
        /** @var Account $model */
        $model = $this->validate($model, $params);
        $this->checkAccess($this->action->id, $model);

        return $this->accountService->createUpdate($model);
    }

    /**
     * @param  int  $id
     * @return Account
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function actionUpdate(int $id): Account
    {
        $params = Yii::$app->request->bodyParams;
        if (!$model = AccountService::findOne($id)) {
            throw new NotFoundHttpException();
        }

        if (data_get($params, 'type') == AccountType::CREDIT_CARD) {
            $model->setScenario(AccountType::CREDIT_CARD);
        }
        /** @var Account $model */
        $model = $this->validate($model, $params);
        $this->checkAccess($this->action->id, $model);

        return $this->accountService->createUpdate($model);
    }

    /**
     * @param  int  $id
     * @return array
     * @throws NotFoundHttpException|InvalidConfigException
     */
    public function actionBalancesTrend(int $id): array
    {
        if (!$model = AccountService::findOne($id)) {
            throw new NotFoundHttpException();
        }
        if (!in_array($model->user_id, UserService::getCurrentMemberIds())) {
            throw new InvalidConfigException('You are not allowed to access this account');
        }
        $endDate = DateHelper::toDate('-1 month');
        return $this->accountService->balancesTrend($model, $endDate);
    }


    /**
     * @return array
     * @throws Exception
     */
    public function actionTypes(): array
    {
        $items = [];
        $texts = AccountType::texts();
        foreach (AccountType::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }

    /**
     * @return array
     */
    public function actionOverview(): array
    {
        $userIds = UserService::getCurrentMemberIds();
        $balanceCentSum = Account::find()
            ->where(['user_id' => $userIds, 'exclude_from_stats' => false])
            ->sum('balance_cent');
        $items['net_asset'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $balanceCentSum = Account::find()
            ->where(['user_id' => $userIds, 'exclude_from_stats' => false])
            ->andWhere(['>', 'balance_cent', 0])
            ->sum('balance_cent');
        $items['total_assets'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $balanceCentSum = Account::find()
            ->where(['user_id' => $userIds, 'exclude_from_stats' => false])
            ->andWhere(['<', 'balance_cent', 0])
            ->sum('balance_cent');
        $items['liabilities'] = $balanceCentSum ? Setup::toYuan($balanceCentSum) : 0;

        $items['count'] = Account::find()
            ->where(['user_id' => $userIds, 'exclude_from_stats' => false])
            ->count('id');

        return $items;
    }
}
