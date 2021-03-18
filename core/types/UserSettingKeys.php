<?php

namespace app\core\types;

class UserSettingKeys
{
    public const DAILY_REPORT = 'daily_report';
    public const WEEKLY_REPORT = 'weekly_report';
    public const MONTHLY_REPORT = 'monthly_report';

    public static function items(): array
    {
        return [
            self::DAILY_REPORT,
            self::WEEKLY_REPORT,
            self::MONTHLY_REPORT,
        ];
    }
}
