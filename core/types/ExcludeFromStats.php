<?php

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
