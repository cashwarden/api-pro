<?php

namespace app\modules\v1\controllers;

use app\core\models\Budget;

/**
 * Tag controller for the `v1` module
 */
class BudgetController extends ActiveController
{
    public $modelClass = Budget::class;
    public $defaultOrder = ['id' => SORT_DESC];
    public $partialMatchAttributes = ['name'];
}
