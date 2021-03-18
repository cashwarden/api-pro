<?php

namespace app\core\types;

class TelegramKeyword
{
    public const BIND = '/bind';
    public const START = '/start';
    public const CMD = '/cmd';
    public const HELP = '/help';
    public const REPORT = '/report';
    public const TODAY = '/' . AnalysisDateType::TODAY;
    public const YESTERDAY = '/' . AnalysisDateType::YESTERDAY;
    public const LAST_MONTH = '/' . AnalysisDateType::LAST_MONTH;
    public const CURRENT_MONTH = '/' . AnalysisDateType::CURRENT_MONTH;
    public const PASSWORD_RESET = '/password_reset';

    /**
     * @return string[]
     */
    public static function items()
    {
        return [
            self::BIND,
            self::START,
            self::CMD,
            self::HELP,
            self::REPORT,
            self::TODAY,
            self::YESTERDAY,
            self::LAST_MONTH,
            self::CURRENT_MONTH,
            self::PASSWORD_RESET,
        ];
    }
}
