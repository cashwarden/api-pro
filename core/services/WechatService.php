<?php

namespace app\core\services;

use app\core\traits\ServiceTrait;

class WechatService
{
    use ServiceTrait;

    public function getConfig(): array
    {
        $logFile = \Yii::getAlias('@runtime/logs/easywechat/' . date('Ymd') . '.log');
        return [
            'app_id' => params('wechatAppId'),
            'secret' => params('wechatAppSecret'),
            'token' => params('wechatToken'),
            'log' => [
                'level' => 'warning',
                'file' => $logFile,
            ],
        ];
    }
}
