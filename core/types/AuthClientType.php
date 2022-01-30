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

class AuthClientType extends BaseType
{
    public const TELEGRAM = 1;
    public const WECHAT = 2;

    public static function names(): array
    {
        return [
            self::TELEGRAM => 'telegram',
            self::WECHAT => 'wechat',
        ];
    }
}
