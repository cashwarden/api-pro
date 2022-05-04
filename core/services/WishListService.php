<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\models\WishList;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

class WishListService
{
    /**
     * @param  int  $id
     * @param  string  $status
     * @return WishList
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function updateStatus(WishList $wishList, string $status): WishList
    {
        $wishList->load($wishList->toArray(), '');
        $wishList->status = $status;
        if (!$wishList->save(false)) {
            throw new Exception(Setup::errorMessage($wishList->firstErrors));
        }
        return $wishList;
    }


    /**
     * @param  int  $id
     * @return WishList
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): WishList
    {
        $userIds = UserService::getCurrentMemberIds();
        if (!$model = WishList::find()->where(['id' => $id, 'user_id' => $userIds])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }
}
