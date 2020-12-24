<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\models\WishList;
use app\core\requests\WishListUpdateStatusRequest;
use app\core\services\LedgerService;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use Yii;
use yii\db\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * WishList controller for the `v1` module
 */
class WishListController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = WishList::class;
    public $defaultOrder = ['id' => SORT_DESC];
    public $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return WishList
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     */
    public function actionUpdateStatus(int $id): WishList
    {
        $params = Yii::$app->request->bodyParams;
        $model = new WishListUpdateStatusRequest();
        /** @var WishListUpdateStatusRequest $model */
        $model = $this->validate($model, $params);

        return $this->wishListService->updateStatus($id, $model->status);
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException|\app\core\exceptions\UserNotProException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        UserProService::checkAccess($this->modelClass, $action, $model);
        if (in_array($action, ['delete', 'update'])) {
            LedgerService::checkAccessOnType($model->ledger_id, $model->user_id, $action);
            LedgerService::checkAccess($model->ledger_id, RuleControlHelper::EDIT);
        }
    }
}
