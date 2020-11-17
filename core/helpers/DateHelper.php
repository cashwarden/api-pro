<?php

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
    public static function toDate($value)
    {
        $value = is_numeric($value) ? $value : strtotime($value);
        return Yii::$app->formatter->asDatetime($value, 'php:Y-m-d');
    }


    /**
     * 最近N天的数组
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
            $items[self::toDate($newDate)] = 0;
        }
        return $items;
    }
}
