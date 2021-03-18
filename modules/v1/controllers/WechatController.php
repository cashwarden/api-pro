<?php

namespace app\modules\v1\controllers;

use app\core\traits\ServiceTrait;
use EasyWeChat\Factory;
use yiier\graylog\Log;

class WechatController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public $noAuthActions = ['login'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    public function actionLogin(string $code)
    {
        $config = $this->wechatService->getConfig();
        $app = Factory::miniProgram($config);
        $s = $app->auth->session($code);
        Log::error('test', $s, [$code]);
        return $s;
    }
}
