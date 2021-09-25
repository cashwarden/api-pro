<?php

namespace app\core\types;

use Yii;

class CurrencyStatus extends BaseStatus
{
    public static function texts()
    {
        return [
            self::ACTIVE => Yii::t('app', 'Normal status'),
            self::UNACTIVATED => Yii::t('app', 'Frozen state'),
        ];
    }
}
