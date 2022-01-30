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

class LedgerType extends BaseType
{
    public const GENERAL = 1;
    public const SHARE = 2;
    public const AA = 3;

    public static function names(): array
    {
        return [
            self::GENERAL => 'general_ledger',
            self::SHARE => 'share_ledger',
            self::AA => 'AA_ledger',
        ];
    }

    public static function texts()
    {
        return [
            self::GENERAL => \Yii::t('app', 'General Ledger'),
            self::SHARE => \Yii::t('app', 'Share Ledger'),
            self::AA => \Yii::t('app', 'AA Ledger'),
        ];
    }
}
