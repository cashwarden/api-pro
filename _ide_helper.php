<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication
     */
    public static $app;
}

/**
 * @property Mis\IdeHelper\IdeHelper $ideHelper
 * @property yiier\helpers\RequestId $requestId
 * @property light\hashids\Hashids $hashids
 * @property yii\caching\FileCache $cache
 * @property yii\db\Connection $db
 * @property hightman\xunsearch\Connection $xunsearch
 * @property yiier\userSetting\UserSetting $userSetting
 * @property yii\queue\db\Queue $queue
 * @property yii\web\Response $response
 * @property Guanguans\YiiPay\Pay $pay
 * @property sizeg\jwt\Jwt $jwt
 * @property yii\web\User $user
 * @property yii\swiftmailer\Mailer $mailer
 */
abstract class BaseApplication extends \yii\base\Application
{
}
