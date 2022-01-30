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

class LedgerMemberRule extends BaseType
{
    public const OWNER = 1;
    public const EDITOR = 2;
    public const VIEWER = 3;

    public static function names(): array
    {
        return [
            self::OWNER => 'owner',
            self::EDITOR => 'editor',
            self::VIEWER => 'viewer',
        ];
    }

    public static function texts(): array
    {
        return [
            self::OWNER => \Yii::t('app', 'Owner'),
            self::EDITOR => \Yii::t('app', 'Editor'),
            self::VIEWER => \Yii::t('app', 'Viewer'),
        ];
    }
}
