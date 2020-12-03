<?php

namespace app\core\types;

class BudgetPeriod extends BaseType
{
    public const DAY = 1;
    public const WEEK = 2;
    public const MONTH = 3;
    public const YEAR = 4;
    public const ONE_TIME = 10;


    public static function names(): array
    {
        return [
            self::DAY => 'day',
            self::WEEK => 'week',
            self::MONTH => 'month',
            self::YEAR => 'year',
            self::ONE_TIME => 'one_time',
        ];
    }

    public static function texts()
    {
        return [
            self::DAY => '每天',
            self::WEEK => '每周',
            self::MONTH => '每月',
            self::YEAR => '每年',
            self::ONE_TIME => '一次性'
        ];
    }
}
