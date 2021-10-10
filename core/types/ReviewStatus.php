<?php

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
