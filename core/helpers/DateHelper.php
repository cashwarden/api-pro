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

use Yii;
use yii\base\InvalidConfigException;

class DateHelper
{
    /**
     * @param string|int $value
     * @return string
     * @throws InvalidConfigException
     */
    public static function toDate($value): string
    {
        $value = is_numeric($value) ? $value : strtotime($value);
        return Yii::$app->formatter->asDatetime($value, 'php:Y-m-d');
    }

    /**
     * @param string|int $value
     * @param string $format
     * @return string
     * @throws InvalidConfigException
     */
    public static function toDateTime($value, string $format = 'php:Y-m-d H:i'): string
    {
        $value = is_numeric($value) ? $value : strtotime($value);
        return Yii::$app->formatter->asDatetime($value, $format);
    }


    /**
     * 最近N天的数组.
     * @param string|int $endDate
     * @param int|null $beginDate
     * @return array
     * @throws InvalidConfigException
     */
    public static function getMonthRange($endDate, int $beginDate = null)
    {
        $beginDate = $beginDate ?: time();
        $endDate = is_numeric($endDate) ? $endDate : strtotime($endDate);
        $days = ceil(abs($beginDate - $endDate) / 86400);
        $items = [];
        for ($i = 0; $i < $days; $i++) {
            $newDate = strtotime("-{$i} days", $beginDate);
            array_push($items, self::toDate($newDate));
            // $items[self::toDate($newDate)] = 0;
        }
        return $items;
    }
}
