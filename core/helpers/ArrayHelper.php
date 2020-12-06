<?php

namespace app\core\helpers;

class ArrayHelper
{
    /**
     * @param $haystack
     * @param array|string $needle
     * @return false|int
     */
    public static function strPosArr($haystack, $needle)
    {
        if (!is_array($needle)) {
            $needle = [$needle];
        }
        foreach ($needle as $what) {
            if (($pos = strpos($haystack, $what)) !== false) {
                return $pos;
            }
        }
        return false;
    }

    /**
     * @param $haystack
     * @param array $needles
     * @param int $offset
     * @return false|mixed
     */
    public static function strPosArray($haystack, $needles = [], $offset = 0): bool
    {
        $chr = [];
        foreach ($needles as $needle) {
            $res = strpos($haystack, $needle, $offset);
            if ($res !== false) {
                $chr[$needle] = $res;
            }
        }
        if (empty($chr)) {
            return false;
        }
        return min($chr);
    }
}
