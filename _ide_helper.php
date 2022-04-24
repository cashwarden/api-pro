<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
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
 * @property yiier\hashids\Hashids $hashids
 * @property yii\caching\FileCache $cache
 * @property yii\db\Connection $db
 * @property hightman\xunsearch\Connection $xunsearch
 * @property yiier\userSetting\UserSetting $userSetting
 * @property yii\queue\db\Queue $queue
 * @property leinonen\Yii2Monolog\Yii2Monolog $monolog
 * @property Guanguans\YiiEvent\Event $event
 * @property yii\web\Response $response
 * @property Guanguans\YiiPay\Pay $pay
 * @property bizley\jwt\Jwt $jwt
 * @property yii\web\User $user
 * @property yii\swiftmailer\Mailer $mailer
 */
abstract class BaseApplication extends \yii\base\Application
{
}
