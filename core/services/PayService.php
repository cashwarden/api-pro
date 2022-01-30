<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\models\UserProRecord;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;

class PayService extends BaseObject
{
    /**
     * @param UserProRecord $record
     * @param $price
     * @return mixed
     * @throws Exception
     */
    public function alipay(UserProRecord $record, $price)
    {
        $order = [
            'out_trade_no' => $record->out_sn . '_' . $record->user_id,
            'total_amount' => $price,
            'subject' => '1年 Pro 会员 - ' . \Yii::$app->name,
        ];

        $alipay = Yii::$app->pay->getAlipay()->scan($order); // 扫码支付

        if ($alipay->code == 10000) {
            return $alipay->qr_code;
        }
        throw new Exception($alipay->sub_msg);
    }
}
