<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\helpers\RuleControlHelper;
use app\core\models\WishList;
use app\core\requests\UpdateStatus;
use app\core\services\LedgerService;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use app\core\types\WishListStatus;
use Yii;
use yii\db\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * WishList controller for the `v1` module.
 */
class WishListController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = WishList::class;
    public array $defaultOrder = ['id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return WishList
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws InternalException
     */
    public function actionUpdateStatus(int $id): WishList
    {
        $params = Yii::$app->request->bodyParams;
        $model = new UpdateStatus(WishListStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->wishListService->updateStatus($id, $model->status);
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException|UserNotProException|InvalidArgumentException
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
