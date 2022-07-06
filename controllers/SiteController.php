<?php

declare(strict_types=1);
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\controllers;

use app\core\exceptions\PayException;
use app\core\traits\ServiceTrait;
use Yii;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;

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
            if (!$exception instanceof NotFoundHttpException) {
                Yii::error([
                    'request_id' => Yii::$app->requestId->id,
                    'exception' => $exception->getMessage(),
                    'line' => $exception->getLine(),
                    'file' => $exception->getFile(),
                    'method' => Yii::$app->request->method,
                ], 'response_data_error');
            }

            return ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
        return [];
    }

    /**
     * @throws \Exception
     */
    public function actionPayNotifyUrl(): bool
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
                    $this->userProService->paySuccess($orderNo, ['user_id' => $userId], $data);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        } catch (\Exception $e) {
            Yii::error('pay_notify_data', $data ?? $alipay);
            Yii::error('pay_notify_data_error', $e);
        }

        $alipay->success()->send();
        Yii::$app->end();
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
            Yii::error('交易失败', $pay ?? []);
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
                'telegram_bot_name' => params('telegramBotName'),
                'currencies' => [
                    'CNY' => '人民币',
                    'USD' => '美元',
                    'TWB' => '台币',
                    'HKD' => '港币',
                    'JPY' => '日元',
                    'EUR' => '欧元',
                    'GBP' => '英镑',
                    'CAD' => '加元',
                    'AUD' => '澳元',
                    'SGD' => '新加坡元',
                    'THB' => '泰铢',
                    'KRW' => '韩元',
                    'PHP' => '菲律宾比索',
                    'IDR' => '印尼盾',
                    'VND' => '越南盾',
                ],
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
                            'text' => '日历图',
                            'link' => '/calendar/index',
                            'icon' => 'anticon-calendar',
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
                        [
                            'text' => '货币',
                            'link' => '/currency/index',
                            'icon' => 'anticon-pound',
                        ],
                        [
                            'text' => '愿望清单',
                            'link' => '/wish-list/index',
                            'icon' => 'anticon-unordered-list',
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
                            'text' => '成员',
                            'link' => '/member/index',
                            'icon' => 'anticon-user',
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
                        [
                            'text' => '投资',
                            'link' => '/assets/index',
                            'icon' => 'anticon-wallet',
                        ],
                    ],
                ],
            ],
        ];
    }
}
