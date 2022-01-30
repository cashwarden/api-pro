<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\requests;

class WechatLogin extends \yii\base\Model
{
    public $code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code'], 'trim'],
            [['code'], 'string'],
            [['code'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => t('app', 'Wechat Login Code'),
        ];
    }
}
