<?php

namespace app\modules\v1\controllers;

use app\core\models\Tag;

/**
 * Tag controller for the `v1` module
 */
class TagController extends ActiveController
{
    public $modelClass = Tag::class;
    public array $defaultOrder = ['count' => SORT_DESC, 'id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];
}
