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

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Category;
use app\core\services\AnalysisService;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\TransactionType;

/**
 * Category controller for the `v1` module.
 */
class CategoryController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Category::class;
    public array $defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];
    public array $stringToIntAttributes = ['transaction_type' => TransactionType::class];

    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function actionAnalysis(): array
    {
        $transactionType = request('transaction_type', TransactionType::getName(TransactionType::EXPENSE));
        $dateType = request('date_type', AnalysisDateType::CURRENT_MONTH);
        $date = AnalysisService::getDateRange($dateType);

        return $this->analysisService->getCategoryStatisticalData(
            $date,
            TransactionType::toEnumValue($transactionType),
            request('ledger_id')
        );
    }
}
