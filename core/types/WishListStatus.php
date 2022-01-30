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

class WishListStatus extends BaseType
{
    public const TODO = 1;
    public const DONE = 2;
    public const CANCELLED = 3;

    public static function names(): array
    {
        return [
            self::TODO => 'todo',
            self::DONE => 'done',
            self::CANCELLED => 'cancelled',
        ];
    }

    public static function texts()
    {
        return [
            self::TODO => Yii::t('app', 'Todo Buy'),
            self::DONE => Yii::t('app', 'Done Buy'),
            self::CANCELLED => Yii::t('app', 'Cancelled Buy'),
        ];
    }
}
