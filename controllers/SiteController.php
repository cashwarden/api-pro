<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\exceptions\PayException;
use app\core\traits\ServiceTrait;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yiier\graylog\Log;

class SiteController extends Controller
{
    use ServiceTrait;

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

    /**
     * @return object
     * @throws \Exception
     */
    public function actionPayNotifyUrl(): object
    {
        $alipay = Yii::$app->pay->getAlipay();
        try {
            $data = $alipay->verify()->toArray();
            $tradeStatus = ArrayHelper::getValue($data, 'trade_status');
            if ($tradeStatus == 'TRADE_SUCCESS') {
                $outTradeNo = explode('_', ArrayHelper::getValue($data, 'out_trade_no'));
                $orderNo = current($outTradeNo);
                $userId = end($outTradeNo);
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $this->userService->paySuccess($orderNo, ['user_id' => $userId], $data);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            Log::error('pay_notify_data', $data ?? $alipay);
            Log::error('pay_notify_data_error', $e);
        }

        return $alipay->success()->send();
    }

    /**
     * @return string
     * @throws PayException
     */
    public function actionPayReturnUrl(): string
    {
        try {
            /** @var object $pay */
            $pay = Yii::$app->pay->getAlipay()->verify();
            return 'ok';
        } catch (\Exception $e) {
            Log::error('交易失败', $pay ?? []);
            throw new PayException('交易失败: ' . $e->getMessage());
        }
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
