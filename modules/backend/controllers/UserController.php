<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\backend\controllers;

use app\core\models\User;
use app\core\services\UserProService;
use app\core\traits\ServiceTrait;
use app\modules\backend\models\UpgradeProForm;
use Carbon\Carbon;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    use ServiceTrait;

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()->filterWhere([
                'id' => request('id'),
                'username' => request('username'),
                'email' => request('email'),
            ]),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionUpgradePro($id): string
    {
        $user = $this->findModel($id);
        $this->layout = false;
        $model = new UpgradeProForm();
        $model->user_id = $id;
        $model->username = $user->username;
        $model->date = $user->pro ? Carbon::parse($user->pro->ended_at)->toDateString() : '';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $endedAt = Carbon::parse($model->date)->endOfDay();
            try {
                if (!User::findOne($id)) {
                    throw new \yii\db\Exception('用户不存在');
                }
                UserProService::upgradeToProBySystem($id, $endedAt);
            } catch (\Exception $e) {
                session()->setFlash('error', '操作失败:' . $e->getMessage());
            }
        }
        return $this->renderAjax('upgrade-pro', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Resource model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
