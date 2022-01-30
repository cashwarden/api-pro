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

/**
 * @link https://ant-design.gitee.io/docs/spec/colors-cn First white font background color
 */
class ColorType
{
    public const RED = '#f5222d';
    public const VOLCANO = '#fa541c';
    public const ORANGE = '#fa8c16';
    public const GOLD = '#faad14';
    public const YELLOW = '#d4b106';
    public const LIME = '#a0d911';
    public const GREEN = '#52c41a';
    public const CYAN = '#13c2c2';
    public const BLUE = '#1890ff';
    public const GEEK_BLUE = '#2f54eb';
    public const PURPLE = '#722ed1';
    public const MAGENTA = '#eb2f96';

    /**
     * @return string[]
     */
    public static function items()
    {
        return [
            self::RED,
            self::VOLCANO,
            self::ORANGE,
            self::GOLD,
            self::YELLOW,
            self::LIME,
            self::GREEN,
            self::CYAN,
            self::BLUE,
            self::GEEK_BLUE,
            self::PURPLE,
            self::MAGENTA,
        ];
    }
}
