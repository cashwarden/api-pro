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

class CurrencyType extends BaseType
{
    /**
     * @var string 人民币
     */
    public const CNY_KEY = 'CNY';

    /**
     * @var string 澳大利亚元
     */
    public const AUD_KEY = 'AUD';

    /**
     * @var string 加拿大元
     */
    public const CAD_KEY = 'CAD';

    /**
     * @var string 欧元
     */
    public const EUR_KEY = 'EUR';

    /**
     * @var string 英镑
     */
    public const GBP_KEY = 'GBP';

    /**
     * @var string 日元
     */
    public const JPY_KEY = 'JPY';

    /**
     * @var string 墨西哥比索
     */
    public const MXN_KEY = 'MXN';

    /**
     * @var string 美元
     */
    public const USD_KEY = 'USD';

    /** @var string 阿联酋迪拉姆 */
    public const AED_KEY = 'AED';

    /** @var string 沙特里亚尔 */
    public const SAR_KEY = 'SAR';

    /** @var string 埃及镑 */
    public const EGP_KEY = 'EGP';

    /** @var string 新加坡元 */
    public const SGD_KEY = 'SGD';

    /** @var string 瑞典克朗 */
    public const SEK_KEY = 'SEK';

    /** @var string 港币 */
    public const HKD_KEY = 'HKD';

    /**
     * 当前在使用的货币
     * @return array
     */
    public static function currentUseCodes(): array
    {
        return [
            self::AUD_KEY,
            self::CAD_KEY,
            self::EUR_KEY,
            self::GBP_KEY,
            self::JPY_KEY,
            self::MXN_KEY,
            self::USD_KEY,
            self::CNY_KEY,
            self::AED_KEY,
            self::SAR_KEY,
            self::EGP_KEY,
            self::SGD_KEY,
            self::SEK_KEY,
            self::HKD_KEY,
        ];
    }

    /**
     * 货币名称.
     * @return array
     */
    public static function names(): array
    {
        return [
            self::AUD_KEY => '澳大利亚元',
            self::CAD_KEY => '加拿大元',
            self::EUR_KEY => '欧元',
            self::GBP_KEY => '英镑',
            self::JPY_KEY => '日元',
            self::MXN_KEY => '墨西哥比索',
            self::USD_KEY => '美元',
            self::CNY_KEY => '人民币',
            self::AED_KEY => '阿联酋迪拉姆',
            self::SAR_KEY => '沙特里亚尔',
            self::EGP_KEY => '埃及镑',
            self::SGD_KEY => '新加坡元',
            self::SEK_KEY => '瑞典克朗',
            self::HKD_KEY => '港币',
        ];
    }
}
