<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\rest\Controller;

class SiteController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex(): string
    {
        return 'hello yii';
    }

    /**
     * @return string
     */
    public function actionHealthCheck(): string
    {
        return 'OK';
    }

    /**
     * @return array
     */
    public function actionError(): array
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            Yii::error([
                'request_id' => Yii::$app->requestId->id,
                'exception' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ], 'response_data_error');

            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
        return [];
    }


    public function actionIcons(): array
    {
        return [
            'food',
            'home',
            'bus',
            'game',
            'medicine-chest',
            'clothes',
            'education',
            'investment',
            'baby',
            'expenses',
            'work',
            'income',
            'transfer',
            'adjust',
        ];
    }

    public function actionData(): array
    {
        return [
            'app' => [
                'name' => Yii::$app->name,
                'description' => params('seoDescription'),
                'keywords' => params('seoKeywords'),
                'google_analytics' => params('googleAnalyticsAU'),
                'telegram_bot_name' => params('telegramBotName')
            ],
            'menu' => [
                [
                    'text' => '账本',
                    'group' => false,
                    'children' => [
                        [
                            'text' => '仪表盘',
                            'link' => '/dashboard',
                            'icon' => 'anticon-dashboard',
                        ],
                        [
                            'text' => '记录',
                            'link' => '/record/index',
                            'icon' => 'anticon-database',
                        ],
                        [
                            'text' => '分析',
                            'link' => '/analysis/index',
                            'icon' => 'anticon-area-chart',
                        ],
                        [
                            'text' => '成员',
                            'link' => '/ledger/member',
                            'icon' => 'anticon-user',
                        ],
                        [
                            'text' => '分类',
                            'link' => '/category/index',
                            'icon' => 'anticon-appstore',
                        ],
                        [
                            'text' => '标签',
                            'link' => '/tag/index',
                            'icon' => 'anticon-appstore',
                        ],
                        [
                            'text' => '预算',
                            'link' => '/budget/index',
                            'icon' => 'anticon-funnel-plot',
                        ],
                    ],
                ],
                [
                    'text' => '全局',
                    'group' => true,
                    'children' => [
                        [
                            'text' => '账户',
                            'link' => '/account/index',
                            'icon' => 'anticon-wallet',
                        ],

                        [
                            'text' => '账本',
                            'link' => '/ledger/index',
                            'icon' => 'anticon-account-book',
                        ],

                        [
                            'text' => '定时',
                            'link' => '/recurrence/index',
                            'icon' => 'anticon-field-time',
                        ],
                        [
                            'text' => '规则',
                            'link' => '/rule/index',
                            'icon' => 'anticon-group',
                        ],
                    ]
                ]
            ]
        ];
    }
}
