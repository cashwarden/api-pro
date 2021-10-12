<?php

namespace app\modules\backend\controllers;

use yii\web\Controller;

class SystemController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionTelegram()
    {
        return $this->render('index');
    }
}
