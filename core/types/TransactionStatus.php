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

class TransactionStatus extends BaseType
{
    /** @var int 待入账 */
    public const TODO = 0;

    /** @var int 已入账 */
    public const DONE = 1;

    public static function names(): array
    {
        return [
            self::TODO => 'todo',
            self::DONE => 'done',
        ];
    }
}
