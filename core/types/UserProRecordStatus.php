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

class UserProRecordStatus extends BaseType
{
    /** @var int 待支付 */
    public const TO_BE_PAID = 1;

    /** @var int 已支付 */
    public const PAID = 2;

    /** @var int 已取消 */
    public const CANCELLED = 3;

    public static function names(): array
    {
        return [
            self::TO_BE_PAID => 'to_be_paid',
            self::PAID => 'paid',
            self::CANCELLED => 'cancelled',
        ];
    }
}
