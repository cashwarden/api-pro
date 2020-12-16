<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\models\LedgerMember;
use app\core\models\Recurrence;
use app\core\requests\UpdateStatus;
use app\core\services\LedgerService;
use app\core\traits\ServiceTrait;
use app\core\types\LedgerMemberStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * LedgerMember controller for the `v1` module
 */
class LedgerMemberController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = LedgerMember::class;

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        // 注销系统自带的实现方法
        return $actions;
    }

    /**
     * @param int $id
     * @return Recurrence
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws InvalidConfigException|InternalException
     */
    public function actionUpdateStatus(int $id): Recurrence
    {
        $params = Yii::$app->request->bodyParams;
        $model = new UpdateStatus(LedgerMemberStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->ledgerService->updateStatus($id, $model->status);
    }

    /**
     * @return ActiveDataProvider
     * @throws InvalidArgumentException
     * @throws InternalException
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        if (!$ledgerId = data_get(Yii::$app->request->queryParams, 'ledger_id')) {
            throw new InvalidArgumentException(
                Yii::t('app', '{attribute} cannot be blank.', ['attribute' => 'ledger_id'])
            );
        }
        return parent::prepareDataProvider();
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['delete', 'update'])) {
            LedgerService::checkAccess($model->ledger_id, RuleControlHelper::MANAGE);
        }
    }
}
