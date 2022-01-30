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

class DirectionType extends BaseType
{
    public const EXPENSE = 1;
    public const INCOME = 2;

    public static function names(): array
    {
        return [
            self::EXPENSE => 'expense',
            self::INCOME => 'income',
        ];
    }
}
