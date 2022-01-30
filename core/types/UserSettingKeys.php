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
