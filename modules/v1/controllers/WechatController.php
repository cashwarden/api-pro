<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\requests\WechatLogin;
use app\core\services\LedgerService;
use app\core\traits\ServiceTrait;
use Yii;

class WechatController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public array $noAuthActions = ['login'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Throwable
     * @throws \app\core\exceptions\InternalException
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function actionLogin(): array
    {
        $params = Yii::$app->request->bodyParams;
        /** @var WechatLogin $data */
        $data = $this->validate(new WechatLogin(), $params);

        $openid = $this->wechatService->getOpenid($data->code);
        $authClient = $this->wechatService->login($openid);
        $user = $authClient->user;
        \Yii::$app->user->setIdentity($user);
        $token = $this->userService->getToken();

        return [
            'user' => $user,
            'token' => $token,
            'default_ledger' => LedgerService::getDefaultLedger(),
        ];
    }

    /**
     * @return string
     * @throws \app\core\exceptions\InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public function actionBind(): string
    {
        $params = Yii::$app->request->bodyParams;
        /** @var WechatLogin $data */
        $data = $this->validate(new WechatLogin(), $params);
        $this->wechatService->bind(Yii::$app->user->id, $data->code);
        return 'ok';
    }
}
