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

class UserProRecordSource extends BaseType
{
    public const SYSTEM = 1;
    public const BUY = 2;
    public const INVITE = 3;

    public static function names(): array
    {
        return [
            self::SYSTEM => 'system',
            self::BUY => 'buy',
            self::INVITE => 'invite',
        ];
    }
}
