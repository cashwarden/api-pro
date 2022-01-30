<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\models\Tag;

/**
 * Tag controller for the `v1` module.
 */
class TagController extends ActiveController
{
    public $modelClass = Tag::class;
    public array $defaultOrder = ['count' => SORT_DESC, 'id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];
}
