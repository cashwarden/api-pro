<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\types;

class ReviewStatus extends BaseType
{
    /** @var int 已对账 */
    public const REVIEWED = 1;

    /** @var int 待对账 */
    public const NO_REVIEW = 0;

    public static function names(): array
    {
        return [
            self::REVIEWED => 'reviewed',
            self::NO_REVIEW => 'no_review',
        ];
    }
}
