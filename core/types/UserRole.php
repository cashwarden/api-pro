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
    const ROLE_WRITER = 30;
    const ROLE_READER = 20;
    const ROLE_DISABLED = 10;

    public static function texts(): array
    {
        return [
            self::ROLE_OWNER => Yii::t('app', 'Owner'),
            self::ROLE_WRITER => Yii::t('app', 'Writer'),
            self::ROLE_READER => Yii::t('app', 'Reader'),
            self::ROLE_DISABLED => Yii::t('app', 'Disabled'),
        ];
    }

    public static function names(): array
    {
        return [
            self::ROLE_OWNER => 'owner',
            self::ROLE_WRITER => 'writer',
            self::ROLE_READER => 'reader',
            self::ROLE_DISABLED => 'disabled',
        ];
    }
}
