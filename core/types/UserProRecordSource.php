<?php

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
