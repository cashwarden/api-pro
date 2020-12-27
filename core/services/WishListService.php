<?php

namespace app\core\services;

use app\core\models\WishList;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

class WishListService
{
    /**
     * @param int $id
     * @param string $status
     * @return WishList
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function updateStatus(int $id, string $status): WishList
    {
        $model = $this->findCurrentOne($id);
        $model->load($model->toArray(), '');
        $model->status = $status;
        if (!$model->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }


    /**
     * @param int $id
     * @return WishList
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): WishList
    {
        if (!$model = WishList::find()->where(['id' => $id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }
}