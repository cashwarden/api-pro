<?php

namespace app\core\models;

class Search extends \hightman\xunsearch\ActiveRecord
{
    public static function search($keyword)
    {
        try {
            return self::find()->where($keyword)
                ->asArray()
                ->limit(5000)
                ->all();
        } catch (\Exception $e) {
            return [];
        }
    }
}
