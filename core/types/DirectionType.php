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
