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

class ReimbursementStatus extends BaseType
{
    /** @var int */
    public const NONE = 0;

    /** @var int */
    public const TODO = 1;

    /** @var int */
    public const DONE = 2;

    public static function names(): array
    {
        return [
            self::NONE => 'none',
            self::TODO => 'todo',
            self::DONE => 'done',
        ];
    }

    public static function text(): array
    {
        return [
            self::NONE => \Yii::t('app', 'No reimbursement'),
            self::TODO => \Yii::t('app', 'To be reimbursed'),
            self::DONE => \Yii::t('app', 'Reimbursed'),
        ];
    }
}
