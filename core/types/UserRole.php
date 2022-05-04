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

final class UserRole extends BaseType
{
    const ROLE_OWNER = 50;
    const ROLE_READ_WRITE = 30;
    const ROLE_READ_ONLY = 20;
    const ROLE_DISABLED = 10;

    public static function texts(): array
    {
        return [
            self::ROLE_OWNER => Yii::t('app', 'Owner'),
            self::ROLE_READ_WRITE => Yii::t('app', 'Read/Write'),
            self::ROLE_READ_ONLY => Yii::t('app', 'Read Only'),
            self::ROLE_DISABLED => Yii::t('app', 'Disabled'),
        ];
    }

    public static function names(): array
    {
        return [
            self::ROLE_OWNER => 'owner',
            self::ROLE_READ_WRITE => 'read_write',
            self::ROLE_READ_ONLY => 'read_only',
            self::ROLE_DISABLED => 'disabled',
        ];
    }
}
