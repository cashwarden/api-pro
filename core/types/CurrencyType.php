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

use JetBrains\PhpStorm\Pure;

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

    /** @var string 港币 */
    public const HKD_KEY = 'HKD';

    public const TWB_KEY = 'TWB';
    public const KRW_KEY = 'KRW';
    public const THB_KEY = 'THB';
    public const PHP_KEY = 'PHP';
    public const IDR_KEY = 'IDR';
    public const VND_KEY = 'VND';


    /**
     * 当前在使用的货币
     * @return array
     */
    #[Pure]
    public static function currentUseCodes(): array
    {
        return array_keys(self::names());
    }

    /**
     * 货币名称.
     * @return array
     */
    public static function names(): array
    {
        return [
            self::CNY_KEY => '人民币',
            self::TWB_KEY => '台币',
            self::HKD_KEY => '港币',
            self::USD_KEY => '美元',
            self::AUD_KEY => '澳大利亚元',
            self::CAD_KEY => '加拿大元',
            self::EUR_KEY => '欧元',
            self::GBP_KEY => '英镑',
            self::JPY_KEY => '日元',
            self::MXN_KEY => '墨西哥比索',
            self::AED_KEY => '阿联酋迪拉姆',
            self::SAR_KEY => '沙特里亚尔',
            self::EGP_KEY => '埃及镑',
            self::SGD_KEY => '新加坡元',
            self::THB_KEY => '泰铢',
            self::KRW_KEY => '韩元',
            self::PHP_KEY => '菲律宾比索',
            self::IDR_KEY => '印尼盾',
            self::VND_KEY => '越南盾',
        ];
    }

    public static function items(): array
    {
        $items = [];
        $names = self::names();
        foreach (self::currentUseCodes() as $currentUseCode) {
            array_push($items, ['code' => $currentUseCode, 'name' => $names[$currentUseCode]]);
        }
        return $items;
    }
}
