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

class ExcludeFromStats extends BaseType
{
    /** @var int */
    public const FALSE = 0;

    /** @var int */
    public const TRUE = 1;

    public static function names(): array
    {
        return [
            self::FALSE => false,
            self::TRUE => true,
        ];
    }
}
