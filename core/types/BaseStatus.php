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

class BaseStatus extends BaseType
{
    /** @var int 激活 */
    public const ACTIVE = 1;

    /** @var int 未激活状态 */
    public const UNACTIVATED = 0;

    public static function names(): array
    {
        return [
            self::ACTIVE => 'active',
            self::UNACTIVATED => 'unactivated',
        ];
    }
}
