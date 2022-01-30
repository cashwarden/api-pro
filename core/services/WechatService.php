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

use app\core\exceptions\ErrorCodes;
use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\AuthClient;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientType;
use EasyWeChat\Factory;
use yii\base\BaseObject;
use yiier\graylog\Log;

/**
 * @property-read array $config
 */
class WechatService extends BaseObject
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

    /**
     * @param string $code
     * @return string
     * @throws InternalException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function getOpenid(string $code): string
    {
        $app = Factory::miniProgram($this->config);
        $response = $app->auth->session($code);
        if ($openid = data_get($response, 'openid')) {
            return $openid;
        }
        throw new InternalException('获取 Openid 失败');
    }

    /**
     * @param string $openid
     * @return AuthClient
     * @throws InvalidArgumentException
     */
    public function login(string $openid): AuthClient
    {
        $authClient = AuthClient::find()->where(['type' => AuthClientType::WECHAT, 'client_id' => $openid])->one();
        if (!$authClient) {
            Log::error('微信登录失败', $openid);
            throw new InvalidArgumentException('首次请先用账号密码登录', ErrorCodes::NOT_USER_ERROR);
        }
        return $authClient;
    }

    /**
     * @param string $userId
     * @param string $openid
     * @return AuthClient
     * @throws \yii\db\Exception
     */
    public function bind(string $userId, string $openid): AuthClient
    {
        $expand = [
            'client_id' => $openid,
        ];
        return UserService::findOrCreateAuthClient($userId, AuthClientType::WECHAT, $expand);
    }
}
