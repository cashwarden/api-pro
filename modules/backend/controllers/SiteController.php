<?php

namespace app\modules\backend\controllers;

use app\core\models\User;
use app\modules\backend\models\LoginForm;
use Yii;
use yii\web\Response;
use yiier\helpers\DateHelper;

class SiteController extends \yii\web\Controller
{

    /**
     * Renders the index view for the module
     * @return Response|string
     * @throws \Exception
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect('login');
        }
        $start = DateHelper::beginTimestamp();
        $end = DateHelper::endTimestamp();
        $todayUserTotal = User::find()->where(['between', 'created_at', $start, $end])->count();
        return $this->render('index', [
            'todayUserTotal' => $todayUserTotal,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect('index');
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->redirect('login');
    }
}
