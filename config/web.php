<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

use app\core\components\ResponseHandler;
use app\core\models\User;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;

$common = require __DIR__ . '/common.php';
$params = require __DIR__ . '/params.php';
$router = require __DIR__ . '/router.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
        'backend' => [
            'class' => 'app\modules\backend\Module',
        ],
    ],
    'components' => [
        'request' => [
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => env('COOKIE_VALIDATION_KEY'),
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                yii::createObject([
                    'class' => ResponseHandler::class,
                    'event' => $event,
                ])->formatResponse();
            },
        ],
        'pay' => [
            'class' => 'Guanguans\YiiPay\Pay',
            'alipayOptions' => [
                'app_id' => env('ALIPAY_APP_ID'),
                'notify_url' => env('APP_URL') . '/pay-notify-url',
                'return_url' => env('APP_URL') . '/pay-return-url',
                'ali_public_key' => env('ALIPAY_ALI_PUBLIC_KEY'),
                // 加密方式： **RSA2**
                'private_key' => env('ALIPAY_PRIVATE_KEY'),
                'log' => [ // optional
                    'file' => './../runtime/logs/alipay/app.log',
                    'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                    'type' => 'single', // optional, 可选 daily.
                    'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
                ],
                'http' => [ // optional
                    'timeout' => 10.0,
                    'connect_timeout' => 10.0,
                    // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
                ],
                // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
            ],
        ],
        'jwt' => [
            'class' => \bizley\jwt\Jwt::class,
            'signer' => \bizley\jwt\Jwt::HS256,
            'signingKey' => env('JWT_SECRET'),
            'validationConstraints' => [
                new IdentifiedBy(env('APP_NAME')),
                new IssuedBy(env('APP_URL')),
                new LooseValidAt(SystemClock::fromSystemTimezone()),
            ],
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['backend/site/login'],
        ],
        'errorHandler' => [
            // 'class' => '\yii\web\ErrorHandler',
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'hostInfo' => getenv('APP_URL'),
            'rules' => $router,
        ],
    ],
    'params' => $params,
    'container' => [
        'definitions' => [
            \yii\widgets\LinkPager::class => \yii\bootstrap5\LinkPager::class,
        ],
    ],
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
        'panels' => [
            'xunsearch' => [
                'class' => 'hightman\\xunsearch\\DebugPanel',
            ],
        ],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return \yii\helpers\ArrayHelper::merge($common, $config);
