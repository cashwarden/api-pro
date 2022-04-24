<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\models\BudgetConfig;
use app\core\traits\ServiceTrait;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class BudgetConfigController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = BudgetConfig::class;
    public array $defaultOrder = ['id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return BudgetConfig
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy(int $id): BudgetConfig
    {
        return $this->budgetService->copy($id);
    }
}
