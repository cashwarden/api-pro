<?php

namespace app\core\requests;

use app\core\types\WishListStatus;

class WishListUpdateStatusRequest extends \yii\base\Model
{
    public $status;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            ['status', 'in', 'range' => WishListStatus::names()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'status' => t('app', 'Status'),
        ];
    }
}
