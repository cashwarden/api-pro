<?php

namespace app\modules\v1\controllers;

use app\core\requests\WechatLogin;
use app\core\traits\ServiceTrait;
use EasyWeChat\Factory;
use Yii;
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

    public function actionLogin()
    {
        $params = Yii::$app->request->bodyParams;
        /** @var WechatLogin $data */
        $data = $this->validate(new WechatLogin(), $params);
        $config = $this->wechatService->getConfig();
        $app = Factory::miniProgram($config);
        $s = $app->auth->session($data->code);
        Log::error('test', $s, [$data->code]);
        return $s;
    }
}
