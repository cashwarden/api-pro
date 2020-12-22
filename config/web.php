<?php

use app\core\components\ResponseHandler;
use app\core\models\User;

$common = require(__DIR__ . '/common.php');
$params = require __DIR__ . '/params.php';

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
    ],
    'components' => [
        'request' => [
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => env('COOKIE_VALIDATION_KEY')
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
            'alipayOption' => [
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
                    'timeout' => 5.0,
                    'connect_timeout' => 5.0,
                    // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
                ],
                // 'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
            ],
        ],
        'jwt' => [
            'class' => \sizeg\jwt\Jwt::class,
            'key' => env('JWT_SECRET'),
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
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
            'rules' => [
                "POST <module>/<alias:login|join>" => '<module>/user/<alias>',
                "POST <module>/token/refresh" => '<module>/user/refresh-token',
                "POST <module>/transactions/by-description" => '<module>/transaction/create-by-description',
                "POST <module>/rules/<id:\d+>/copy" => '<module>/rule/copy',
                "PUT <module>/rules/<id:\d+>/status" => '<module>/rule/update-status',
                "GET <module>/accounts/types" => '<module>/account/types',
                "GET <module>/accounts/<id:\d+>/balances/trend" => '<module>/account/balances-trend',
                "GET <module>/accounts/overview" => '<module>/account/overview',
                "POST <module>/reset-token" => '<module>/user/reset-token',

                "GET <module>/users/auth-clients" => '<module>/user/get-auth-clients',
                'POST <module>/users/confirm' => '<module>/user/confirm',
                'POST <module>/users/send-confirmation' => '<module>/user/send-confirmation',
                'POST <module>/users/me' => '<module>/user/me-update',
                'GET <module>/users/me' => '<module>/user/me',
                'POST <module>/users/password-reset' => '<module>/user/password-reset',
                'POST <module>/users/change-password' => '<module>/user/change-password',
                'POST <module>/users/password-reset-token-verification' =>
                    '<module>/user/password-reset-token-verification',
                'POST <module>/users/password-reset-request' => '<module>/user/password-reset-request',
                'POST <module>/users/upgrade-to-pro-request' => '<module>/user/upgrade-to-pro-request',
                "GET <module>/users/user-pro-record/<out_sn:\w+>" => '<module>/user/get-user-pro-record',

                "GET <module>/transactions/<alias:types|export>" => '<module>/transaction/<alias>',
                "POST <module>/transactions/upload" => '<module>/transaction/upload',
                "GET <module>/records/overview" => '<module>/record/overview',
                "GET <module>/categories/analysis" => '<module>/category/analysis',
                "GET <module>/records/analysis" => '<module>/record/analysis',
                "GET <module>/records/sources" => '<module>/record/sources',
                "PUT <module>/recurrences/<id:\d+>/status" => '<module>/recurrence/update-status',
                "GET <module>/recurrences/frequencies" => '<module>/recurrence/frequency-types',

                "GET <module>/site-config" => '/site/data',
                "POST pay-notify-url" => '/site/pay-notify-url',
                "POST pay-return-url" => '/site/pay-return-url',
                "GET <module>/<alias:icons>" => '/site/<alias>',
                "GET health-check" => 'site/health-check',

                "GET <module>/ledgers/types" => '<module>/ledger/types',
                "GET <module>/ledgers/categories" => '<module>/ledger/categories',
                "GET <module>/ledgers/token/<token:\w+>" => '<module>/ledger/view-by-token',
                "POST <module>/ledgers/join/<token:\w+>" => '<module>/ledger/join-by-token',
                "POST <module>/ledger/members" => '<module>/ledger/inviting-member',
                "GET <module>/ledger/members" => '<module>/ledger-member/index',
                "PUT <module>/ledger/members/<id:\d+>" => '<module>/ledger-member/update',

                "POST <module>/budget-configs/<id:\d+>/copy" => '<module>/budget-config/copy',

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => [
                        'v1/account',
                        'v1/category',
                        'v1/rule',
                        'v1/tag',
                        'v1/record',
                        'v1/transaction',
                        'v1/recurrence',
                        'v1/budget-config',
                        'v1/ledger',
                    ]
                ],
                // '<module>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
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
