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

class TelegramKeyword
{
    public const BIND = '/bind';
    public const START = '/start';
    public const TODAY = '/' . AnalysisDateType::TODAY;
    public const YESTERDAY = '/' . AnalysisDateType::YESTERDAY;
    public const LAST_MONTH = '/' . AnalysisDateType::LAST_MONTH;
    public const CURRENT_MONTH = '/' . AnalysisDateType::CURRENT_MONTH;
    public const PASSWORD_RESET = '/password_reset';

    /**
     * @return string[]
     */
    public static function items(): array
    {
        return [
            self::BIND,
            self::START,
            self::TODAY,
            self::YESTERDAY,
            self::LAST_MONTH,
            self::CURRENT_MONTH,
            self::PASSWORD_RESET,
        ];
    }

    /**
     * @return string[]
     */
    public static function commands()
    {
        return [
            self::TODAY => '今日消费报告',
            self::YESTERDAY => '昨日消费报告',
            self::LAST_MONTH => '上个月消费报告',
            self::CURRENT_MONTH => '本月消费报告',
            self::PASSWORD_RESET => '重置密码',
        ];
    }
}
