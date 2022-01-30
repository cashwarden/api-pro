<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\helpers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\types\BaseType;
use yii\base\InvalidConfigException;

class SearchHelper
{
    /**
     * @param string $searchStr
     * @param $typeClassName
     * @return string
     * @throws InvalidArgumentException
     * @throws InvalidConfigException|InternalException
     */
    public static function stringToInt(string $searchStr, $typeClassName): string
    {
        $items = [];
        /** @var BaseType $type */
        $type = \Yii::createObject($typeClassName);
        if (!$type instanceof BaseType) {
            throw new InternalException('search string to Int fail');
        }
        $searchArr = explode(',', $searchStr);
        foreach ($searchArr as $search) {
            $v = trim($search);
            if (in_array($v, $type::names())) {
                $items[] = $type::toEnumValue($v);
            }
        }
        return implode(',', $items);
    }
}
