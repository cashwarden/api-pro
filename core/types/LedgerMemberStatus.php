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

class LedgerMemberStatus extends BaseType
{
    public const WAITING = 0;
    public const NORMAL = 1;
    public const ARCHIVED = 2;

    public static function names(): array
    {
        return [
            self::NORMAL => 'normal',
            self::WAITING => 'waiting',
            self::ARCHIVED => 'archived',
        ];
    }

    public static function texts()
    {
        return [
            self::NORMAL => Yii::t('app', 'Normal status'),
            self::WAITING => Yii::t('app', 'Waiting status'),
            self::ARCHIVED => Yii::t('app', 'Archived status'),
        ];
    }
}
