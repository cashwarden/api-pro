<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

use leinonen\Yii2Monolog\MonologTarget;
use leinonen\Yii2Monolog\Yii2Monolog;
use Monolog\Handler\SyslogUdpHandler;
use yii\log\Logger;
use yii\mutex\MysqlMutex;
use yii\queue\ExecEvent;

return [
    'timeZone' => env('APP_TIME_ZONE', 'Asia/Shanghai'),
    'language' => env('APP_LANGUAGE'),
    'name' => env('APP_NAME'),
    'bootstrap' => ['monolog', 'log', 'ideHelper', \app\core\EventBootstrap::class, 'queue'],
    'components' => [
        'ideHelper' => [
            'class' => 'Mis\IdeHelper\IdeHelper',
            'configFiles' => [
                'config/web.php',
                'config/common.php',
                'config/console.php',
            ],
        ],
        'requestId' => [
            'class' => \yiier\helpers\RequestId::class,
        ],
        'hashids' => [
            'class' => 'light\hashids\Hashids',
            'salt' => env('JWT_SECRET'),
            'alphabet' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
            'minHashLength' => 20,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
            'datetimeFormat' => 'yyyy-MM-dd HH:mm:ss',
            'decimalSeparator' => ',',
            'thousandSeparator' => ' ',
            'currencyCode' => 'CNY',
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host='.env('MYSQL_HOST').';port='.env('MYSQL_PORT').';dbname='.env('MYSQL_DATABASE'),
            'username' => env('MYSQL_USERNAME'),
            'password' => env('MYSQL_PASSWORD'),
            'charset' => 'utf8mb4',
            'enableSchemaCache' => YII_ENV_PROD,
            'schemaCacheDuration' => 60,
            'schemaCache' => 'cache',
        ],
        'xunsearch' => [
            'class' => 'hightman\xunsearch\Connection',
            'iniDirectory' => '@app/config',    // 搜索 ini 文件目录，默认：@vendor/hightman/xunsearch/app
        ],
        'userSetting' => [
            'class' => 'yiier\userSetting\UserSetting',
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db', // DB connection component or its config
            'tableName' => '{{%queue}}', // Table name
            'ttr' => 5 * 60, // Max time for job execution
            'attempts' => 3, // Max number of attempts
            'channel' => 'default', // Queue channel key
            'mutex' => MysqlMutex::class, // Mutex used to sync queries
            'on afterError' => function (ExecEvent $event) {
                Yii::error('队列执行失败1', $event->job);
                Yii::error('队列执行失败2', $event->error);
            },
        ],
        'monolog' => [
            'class' => Yii2Monolog::class,
            'channels' => [
                'myFirstChannel' => [
                    'handlers' => [
                        SyslogUdpHandler::class => [
                            'host' => env('SEMATEXT_HOST'),
                            'ident' => env('SEMATEXT_IDENT'),
                            'level' => \Monolog\Logger::INFO,
                        ],
                    ],
                    'processors' => [
                        function ($record) {
                            $record['extra']['app'] = env('APP_NAME').'_'.env('YII_ENV');
                            $record['extra']['request_id'] = Yii::$app->requestId->id;

                            return $record;
                        },
                    ],
                ],
            ],
        ],
        'event' => [
            'class' => \Guanguans\YiiEvent\Event::class,
            'listen' => [
                \app\core\events\CreateRecordSuccessEvent::class => [
                    \app\core\listeners\SendCreateRecordSuccessTelegram::class,
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@app/core/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                        'app/error' => 'exception.php',
                    ],
                ],
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => MonologTarget::class,
                    'channel' => 'myFirstChannel',
                    'levels' => ['error', 'warning', 'info'],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                ],
//                [
//                    'class' => yiier\graylog\Target::class,
//                    // 日志等级
//                    'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING,
//                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
//                    'categories' => [
//                        'yii\db\*',
//                        'yii\web\HttpException:*',
//                        'application',
//                    ],
//                    'except' => [
//                        'yii\web\HttpException:404',
//                    ],
//                    'transport' => [
//                        'class' => yiier\graylog\transport\UdpTransport::class,
//                        'host' => getenv('GRAYLOG_HOST'),
//                        'chunkSize' => 4321,
//                    ],
//                    'additionalFields' => [
//                        'request_id' => function ($yii) {
//                            return Yii::$app->requestId->id;
//                        },
//                        'user_ip' => function ($yii) {
//                            return ($yii instanceof \yii\console\Application) ? '' : $yii->request->userIP;
//                        },
//                        'tag' => getenv('GRAYLOG_TAG')
//                    ],
//                ],
                [
                    'class' => 'notamedia\sentry\SentryTarget',
                    'dsn' => env('SENTRY_DSN'),
                    'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING,
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    // Write the context information (the default is true):
                    'context' => true,
                    // Additional options for `Sentry\init`:
                    'clientOptions' => ['release' => getenv('GRAYLOG_TAG')],
                ],
//                [
//                    'class' => yiier\graylog\Target::class,
//                    'levels' => Logger::LEVEL_ERROR | Logger::LEVEL_WARNING | Logger::LEVEL_INFO,
//                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
//                    'categories' => [
//                        'graylog'
//                    ],
//                    'except' => [
//                        'yii\web\HttpException:404',
//                    ],
//                    'transport' => [
//                        'class' => yiier\graylog\transport\UdpTransport::class,
//                        'host' => getenv('GRAYLOG_HOST'),
//                        'chunkSize' => 4321,
//                    ],
//                    'additionalFields' => [
//                        'request_id' => function ($yii) {
//                            return Yii::$app->requestId->id;
//                        },
//                        'user_ip' => function ($yii) {
//                            return ($yii instanceof \yii\console\Application) ? '' : $yii->request->userIP;
//                        },
//                        'tag' => getenv('GRAYLOG_TAG')
//                    ],
//                ],
                /**
                 * 错误级别日志：当某些需要立马解决的致命问题发生的时候，调用此方法记录相关信息。
                 * 使用方法：Yii::error().
                 */
                [
                    'class' => 'yiier\helpers\FileTarget',
                    // 日志等级
                    'levels' => ['error'],
                    'except' => [
                        'yii\web\HttpException:404',
                    ],
                    // 被收集记录的额外数据
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    // 指定日志保存的文件名
                    'logFile' => '@app/runtime/logs/error/app.log',
                    // 是否开启日志 (@app/runtime/logs/error/20151223_app.log)
                    'enableDatePrefix' => true,
                ],
                /**
                 * 警告级别日志：当某些期望之外的事情发生的时候，使用该方法。
                 * 使用方法：Yii::warning().
                 */
                [
                    'class' => 'yiier\helpers\FileTarget',
                    // 日志等级
                    'levels' => ['warning'],
                    // 被收集记录的额外数据
                    'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
                    // 指定日志保存的文件名
                    'logFile' => '@app/runtime/logs/warning/app.log',
                    // 是否开启日志 (@app/runtime/logs/warning/20151223_app.log)
                    'enableDatePrefix' => true,
                ],
                [
                    'class' => 'yiier\helpers\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['request'],
                    'logVars' => [],
                    'maxFileSize' => 1024,
                    'logFile' => '@app/runtime/logs/request/app.log',
                    'enableDatePrefix' => true,
                ],
                [
                    'class' => 'yiier\helpers\FileTarget',
                    'levels' => ['warning'],
                    'categories' => ['debug'],
                    'logVars' => [],
                    'maxFileSize' => 1024,
                    'logFile' => '@app/runtime/logs/debug/app.log',
                    'enableDatePrefix' => true,
                ],
            ],
        ],
    ],
];
