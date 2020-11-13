<?php

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
