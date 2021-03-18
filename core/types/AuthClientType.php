<?php

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
