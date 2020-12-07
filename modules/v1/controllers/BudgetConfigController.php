<?php

namespace app\modules\v1\controllers;

use app\core\models\Budget;
use app\core\models\BudgetConfig;

class BudgetConfigController extends ActiveController
{
    public $modelClass = BudgetConfig::class;
    public $defaultOrder = ['id' => SORT_DESC];
    public $partialMatchAttributes = ['name'];
}
