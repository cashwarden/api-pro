<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\helpers\RuleControlHelper;
use app\core\helpers\SearchHelper;
use app\core\models\Ledger;
use app\core\requests\LedgerInvitingMember;
use app\core\services\LedgerService;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use app\core\types\LedgerType;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yiier\helpers\SearchModel;

/**
 * Ledger controller for the `v1` module
 */
class LedgerController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Ledger::class;
    public $partialMatchAttributes = ['name'];
    public $stringToIntAttributes = ['type' => LedgerType::class];


    public function actions()
    {
        return parent::actions();
    }

    /**
     * @return bool
     * @throws InternalException
     * @throws InvalidArgumentException
     */
    public function actionInvitingMember(): bool
    {
        $model = new LedgerInvitingMember();
        $params = Yii::$app->request->bodyParams;
        $this->validate($model, $params);
        $model->validateGroup();

        return $this->ledgerService->invitingMember($model);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes(): array
    {
        $items = [];
        $texts = LedgerType::texts();
        foreach (LedgerType::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }


    public function actionCategories(): array
    {
        return $this->ledgerService->getLedgersCategories();
    }

    /**
     * @param string $token
     * @return Ledger
     * @throws NotFoundHttpException
     */
    public function actionViewByToken(string $token): Ledger
    {
        $model = $this->ledgerService->getLedgerByToken($token);
//        if (!LedgerMember::find()->where(['ledger_id' => $model->id, 'user_id' => Yii::$app->user->id])->exists()) {
//            throw new ForbiddenHttpException(
//                Yii::t('app', 'You can only view data that you\'ve created.')
//            );
//        }
        return $model;
    }

    /**
     * @param string $token
     * @return bool
     * @throws InvalidArgumentException
     * @throws \yii\db\Exception|NotFoundHttpException
     */
    public function actionJoinByToken(string $token): bool
    {
        return $this->ledgerService->joinLedgerByToken($token);
    }

    /**
     * @return ActiveDataProvider
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => $this->defaultOrder,
            'model' => $modelClass,
            'scenario' => 'default',
            'partialMatchAttributes' => $this->partialMatchAttributes,
            'pageSize' => $this->getPageSize()
        ]);

        $params = $this->formatParams(Yii::$app->request->queryParams);
        foreach ($this->stringToIntAttributes as $attribute => $className) {
            if ($type = data_get($params, $attribute)) {
                $params[$attribute] = SearchHelper::stringToInt($type, $className);
            }
        }
        unset($params['sort']);

        $dataProvider = $searchModel->search(['SearchModel' => $params]);
        $dataProvider->query->andWhere(['id' => LedgerService::getLedgerIds()]);
        return $dataProvider;
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     * @throws UserNotProException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        UserProService::checkAccess($this->modelClass, $action, $model);
        if (in_array($action, ['delete', 'update'])) {
            LedgerService::checkAccess($model->id, RuleControlHelper::MANAGE);
        }
    }
}
