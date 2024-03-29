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

abstract class BaseType
{
    abstract public static function names(): array;

    /**
     * @param int $v
     * @return string
     * @throws InvalidArgumentException
     */
    public static function getName(int $v): string
    {
        try {
            return static::names()[$v];
        } catch (\ErrorException $e) {
            throw new InvalidArgumentException(sprintf('Invalid: %s const value %s', __CLASS__, $v));
        }
    }

    /**
     * @param string $v
     * @return int
     * @throws InvalidArgumentException
     */
    public static function toEnumValue(string $v): int
    {
        if ($v === null) {
            throw new InvalidArgumentException('parameter is invalid, value is ' . $v);
        }

        $constants = static::names();

        $key = array_search($v, $constants);

        if ($key === false) {
            throw new InvalidArgumentException('parameter is invalid , value is ' . $v);
        }

        return $key;
    }
}
