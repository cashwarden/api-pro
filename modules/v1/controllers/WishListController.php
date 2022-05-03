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

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\WishList;
use app\core\requests\UpdateStatus;
use app\core\traits\ServiceTrait;
use app\core\types\WishListStatus;
use Yii;
use yii\db\Exception;
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
     * @param  int  $id
     * @return WishList
     * @throws Exception
     * @throws InternalException
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws \app\core\exceptions\UserNotProException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdateStatus(int $id): WishList
    {
        $params = Yii::$app->request->bodyParams;
        $wishList = $this->wishListService->findCurrentOne($id);
        $this->checkAccess($this->action->id, $wishList);
        $model = new UpdateStatus(WishListStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->wishListService->updateStatus($wishList, $model->status);
    }
}
