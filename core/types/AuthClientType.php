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
