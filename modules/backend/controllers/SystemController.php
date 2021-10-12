<?php

namespace app\modules\backend\controllers;

use app\core\services\TelegramService;
use yii\helpers\Url;
use yii\web\Controller;

class SystemController extends Controller
{

    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionInitTelegram(): string
    {
        try {
            $url = Url::to('/v1/telegram/hook', true);
            TelegramService::newClient()->setWebHook($url);
            TelegramService::setMyCommands();
            session()->setFlash('success', '初始化成功');
        } catch (\Throwable $e) {
            session()->setFlash('error', '初始化失败:' . $e->getMessage());
        }

        return $this->render('index');
    }
}
