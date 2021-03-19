<?php

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
