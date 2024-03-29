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

use Yii;

class TransactionType extends BaseType
{
    public const EXPENSE = 1;
    public const INCOME = 2;
    public const TRANSFER = 3;
    public const ADJUST = 4;

    public static function names(): array
    {
        return [
            self::EXPENSE => 'expense',
            self::INCOME => 'income',
            self::TRANSFER => 'transfer',
            self::ADJUST => 'adjust',
        ];
    }

    public static function texts(): array
    {
        return [
            self::EXPENSE => Yii::t('app', 'Expense'),
            self::INCOME => Yii::t('app', 'Income'),
            self::TRANSFER => Yii::t('app', 'Transfer'),
            self::ADJUST => Yii::t('app', 'Adjust Balance'),
        ];
    }
}
