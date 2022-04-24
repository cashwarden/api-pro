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

use app\core\exceptions\InvalidArgumentException;

class AnalysisGroupDateType
{
    public const DAY = 'day';
    public const MONTH = 'month';
    public const YEAR = 'year';

    /**
     * @param string $key
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getValue(string $key)
    {
        $items = [
            self::DAY => '%Y-%m-%d',
            self::MONTH => '%Y-%m',
            self::YEAR => '%Y',
        ];
        try {
            return $items[$key];
        } catch (\ErrorException $e) {
            throw new InvalidArgumentException(sprintf('Invalid: %s const value %s', __CLASS__, $key));
        }
    }
}
