<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

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
