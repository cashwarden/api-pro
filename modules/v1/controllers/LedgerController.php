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

use app\core\models\Ledger;
use app\core\traits\ServiceTrait;
use app\core\types\LedgerType;

/**
 * Ledger controller for the `v1` module.
 */
class LedgerController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Ledger::class;
    public array $partialMatchAttributes = ['name'];
    public array $stringToIntAttributes = ['type' => LedgerType::class];


    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes(): array
    {
        $items = [];
        $texts = LedgerType::texts();
        foreach (LedgerType::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }


    public function actionCategories(): array
    {
        return $this->ledgerService->getLedgersCategories();
    }
}
