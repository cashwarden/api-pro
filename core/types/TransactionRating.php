<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\types;

use Yii;

class TransactionRating extends BaseType
{
    public const MUST = 1;
    public const NEED = 2;
    public const WANT = 3;

    public static function names(): array
    {
        return [
            self::MUST => 'must',
            self::NEED => 'need',
            self::WANT => 'want',
        ];
    }

    public static function texts(): array
    {
        return [
            self::MUST => Yii::t('app', 'Must'),
            self::NEED => Yii::t('app', 'Need'),
            self::WANT => Yii::t('app', 'Want'),
        ];
    }
}
