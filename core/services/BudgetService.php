<?php

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\helpers\DateHelper;
use app\core\models\Account;
use app\core\models\Budget;
use app\core\models\BudgetConfig;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\LedgerMember;
use app\core\requests\LedgerInvitingMember;
use app\core\types\BudgetPeriod;
use app\core\types\ColorType;
use app\core\types\LedgerMemberRule;
use app\core\types\LedgerMemberStatus;
use app\core\types\TransactionType;
use Carbon\Carbon;
use Yii;
use yii\base\BaseObject;
use yii\db\Exception;
use yiier\helpers\Setup;

class BudgetService extends BaseObject
{
    public static function createBudgetConfigAfter(BudgetConfig $budgetConfig)
    {
        $d1 = new Carbon($budgetConfig->started_at);
        $d2 = new Carbon($budgetConfig->ended_at);
        $model = new Budget();
        switch ($budgetConfig->period) {
            case BudgetPeriod::MONTH:
                $diff = $d1->diffInMonths($d2);
                for ($i = 0; $i <= $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addMonth($i);
                    $_model->ended_at = Carbon::parse($budgetConfig->started_at)
                        ->addMonth($i)
                        ->endOfMonth()
                        ->endOfDay();
                    if ($i === $diff) {
                        $_model->ended_at = Carbon::parse($budgetConfig->ended_at)->endOfDay();
                    }
                    if ($i > 0) {
                        $_model->started_at =  $_model->started_at->firstOfMonth();
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
                $diff = $d1->diffInYears($d2) + 1;
                for ($i = 0; $i <= $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addYear($i);
                    $_model->ended_at = Carbon::parse($budgetConfig->started_at)->addYear($i)->endOfYear()->endOfDay();
                    if ($i === $diff) {
                        $_model->ended_at = Carbon::parse($budgetConfig->ended_at)->endOfDay();
                    }
                    if ($i > 0) {
                        $_model->started_at =  $_model->started_at->firstOfYear();
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
    }
}
