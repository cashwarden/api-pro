<?php

namespace app\core\services;

use app\core\models\Budget;
use app\core\models\BudgetConfig;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\types\BudgetPeriod;
use Carbon\Carbon;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\db\Expression;
use yiier\helpers\Setup;

class BudgetService extends BaseObject
{
    /**
     * @param BudgetConfig $budgetConfig
     * @throws Exception
     */
    public static function createUpdateBudgetConfigAfter(BudgetConfig $budgetConfig): void
    {
        $d1 = new Carbon($budgetConfig->started_at);
        $d2 = new Carbon($budgetConfig->ended_at);
        $model = new Budget();
        switch ($budgetConfig->period) {
            case BudgetPeriod::MONTH:
                $diff = ceil($d1->floatDiffInMonths($d2));
                if ($diff == 1 && $d1->month !== $d2->month) {
                    $diff = 2;
                }
                // dd($diff);
                for ($i = 0; $i < $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addMonths($i);
                    if ($i < $diff - 1) {
                        $_model->ended_at = Carbon::parse($budgetConfig->started_at)
                            ->addMonths($i)
                            ->endOfMonth()
                            ->endOfDay();
                    } else {
                        $_model->ended_at = Carbon::parse($budgetConfig->ended_at)->endOfDay();
                    }
                    if ($i > 0) {
                        $_model->started_at = $_model->started_at->firstOfMonth();
                    }
                    $_model->user_id = $budgetConfig->user_id;
                    $_model->budget_config_id = $budgetConfig->id;
                    $_model->actual_amount_cent = 0;
                    $_model->budget_amount_cent = $budgetConfig->amount_cent;
                    if (!$_model->save(false)) {
                        throw new Exception(Setup::errorMessage($_model->firstErrors));
                    }
                }
                break;
            case BudgetPeriod::YEAR:
                $diff = ceil($d1->floatDiffInYears($d2));
                if ($diff == 1 && $d1->year !== $d2->year) {
                    $diff = 2;
                }
                for ($i = 0; $i < $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addYears($i);
                    if ($i < $diff - 1) {
                        $_model->ended_at = Carbon::parse($budgetConfig->started_at)
                            ->addMonths($i)
                            ->endOfYear()
                            ->endOfDay();
                    } else {
                        $_model->ended_at = Carbon::parse($budgetConfig->ended_at)->endOfDay();
                    }
                    if ($i > 0) {
                        $_model->started_at = $_model->started_at->firstOfYear();
                    }
                    $_model->user_id = $budgetConfig->user_id;
                    $_model->budget_config_id = $budgetConfig->id;
                    $_model->actual_amount_cent = 0;
                    $_model->budget_amount_cent = $budgetConfig->amount_cent;
                    if (!$_model->save(false)) {
                        throw new Exception(Setup::errorMessage($_model->firstErrors));
                    }
                }
                break;
            default:
                // 一次性
                $model->started_at = $budgetConfig->started_at;
                $model->ended_at = $budgetConfig->ended_at;
                $model->user_id = $budgetConfig->user_id;
                $model->budget_config_id = $budgetConfig->id;
                $model->actual_amount_cent = 0;
                $model->budget_amount_cent = $budgetConfig->amount_cent;
                if (!$model->save(false)) {
                    throw new Exception(Setup::errorMessage($model->firstErrors));
                }
                # code...
                break;
        }
        // 计算预算
        self::calculationAmount($budgetConfig);
    }


    /**
     * @param BudgetConfig $budgetConfig
     * @throws Exception
     */
    public static function calculationAmount(BudgetConfig $budgetConfig): void
    {
        $budgets = Budget::find()
            ->where(['budget_config_id' => $budgetConfig->id, 'relation_budget_id' => null])
            ->all();
        $remainingBudgetAmountCent = 0;
        /** @var Budget $budget */
        foreach ($budgets as $k => $budget) {
            $categoryIds = explode(',', $budgetConfig->category_ids);
            $query = Transaction::find()
                ->where(['user_id' => $budgetConfig->user_id, 'type' => $budgetConfig->transaction_type])
                ->andWhere(['between', 'date', $budget->started_at, $budget->ended_at])
                ->andFilterWhere(['ledger_id' => $budgetConfig->ledger_id, 'category_id' => $categoryIds,]);
            if ($tag = $budgetConfig->include_tags) {
                $query->andWhere(new Expression('FIND_IN_SET(:tag, tags)'))
                    ->addParams([':tag' => $tag]);
            }
            if ($tag = $budgetConfig->exclude_tags) {
                $query->andWhere(new Expression('NOT FIND_IN_SET(:tag, tags)'))
                    ->addParams([':tag' => $tag]);
            }
            if ($actualAmountCent = $query->sum('amount_cent')) {
                $budget->actual_amount_cent = $actualAmountCent;
                $recordIds = Record::find()
                    ->where(['user_id' => $budgetConfig->user_id, 'transaction_id' => $query->column()])
                    ->column();
                $budget->record_ids = implode(',', $recordIds);
            }

            if (!$k) {
                $remainingBudgetAmountCent = $budgetConfig->init_amount_cent;
            }
            if ($budgetConfig->rollover) {
                $budget->budget_amount_cent = $remainingBudgetAmountCent + $budgetConfig->amount_cent;
            }
            if (!$budget->save(false)) {
                throw new Exception(Setup::errorMessage($budget->firstErrors));
            }
            $remainingBudgetAmountCent = $budget->budget_amount_cent - $budget->actual_amount_cent;
        }
    }
}
