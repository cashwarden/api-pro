<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\traits;

use yii\web\ForbiddenHttpException;

trait CheckAccessTrait
{
    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['delete', 'update', 'view'])) {
            if ($model->user_id !== \Yii::$app->user->id) {
                throw new ForbiddenHttpException(
                    t('app', 'You can only ' . $action . ' data that you\'ve created.')
                );
            }
        }
    }
}
