<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\models\Budget;
use app\core\models\BudgetConfig;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\types\BudgetPeriod;
use app\core\types\ReimbursementStatus;
use Carbon\Carbon;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\db\Expression;
use yii\web\NotFoundHttpException;
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
                $diff = $d1->diffInMonths($d2);
                for ($i = 0; $i <= $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addMonths($i);
                    $_model->ended_at = Carbon::parse($budgetConfig->started_at)
                        ->addMonths($i)
                        ->endOfMonth()
                        ->endOfDay();
                    if ($i === $diff) {
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
                $diff = $d1->diffInYears($d2);
                for ($i = 0; $i <= $diff; $i++) {
                    $_model = clone $model;
                    $_model->started_at = Carbon::parse($budgetConfig->started_at)->addYears($i);
                    $_model->ended_at = Carbon::parse($budgetConfig->started_at)->addYears($i)->endOfYear()->endOfDay();
                    if ($i === $diff) {
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
                // code...
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
        $ledgerId = $budgetConfig->ledger_id;
        $baseConditions = ['user_id' => LedgerService::getLedgerMemberUserIds($ledgerId), 'ledger_id' => $ledgerId];
        $endDate = Transaction::find()->where($baseConditions)->max('date');
        if (!$endDate) {
            return;
        }
        $budgets = Budget::find()
            ->where(['budget_config_id' => $budgetConfig->id, 'relation_budget_id' => null])
            ->andWhere(['<=', 'started_at', $endDate])
            ->all();
        $remainingBudgetAmountCent = 0;
        /** @var Budget $budget */
        foreach ($budgets as $k => $budget) {
            $categoryIds = explode(',', $budgetConfig->category_ids);
            $query = Transaction::find()
                ->where($baseConditions + ['type' => $budgetConfig->transaction_type])
                ->andWhere(['between', 'date', $budget->started_at, $budget->ended_at])
                ->andFilterWhere(['ledger_id' => $budgetConfig->ledger_id, 'category_id' => $categoryIds]);
            if ($tag = $budgetConfig->include_tags) {
                $query->andWhere(new Expression('FIND_IN_SET(:tag, tags)'))
                    ->addParams([':tag' => $tag]);
            }
            if ($tag = $budgetConfig->exclude_tags) {
                $query->andWhere(new Expression('NOT FIND_IN_SET(:tag, COALESCE(tags, \'\'))'))
                    ->addParams([':tag' => $tag]);
            }
            if ($query->sum('amount_cent')) {
                $recordQuery = Record::find()
                    ->where(['user_id' => $budgetConfig->user_id])
                    ->andWhere([
                        'transaction_id' => array_map('intval', $query->column()),
                        'exclude_from_stats' => (int) false,
                        'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
                    ]);
                $recordIds = $recordQuery->column();
                $budget->actual_amount_cent = $recordQuery->sum('amount_cent');
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


    /**
     * @param int $ledgerId
     * @param string $datetime
     * @throws Exception
     */
    public static function updateBudgetActualAmount(int $ledgerId, string $datetime)
    {
        $budgetConfigs = BudgetConfig::find()->where(['ledger_id' => $ledgerId])
            ->andWhere(['<=', 'started_at', $datetime])
            ->all();

        foreach ($budgetConfigs as $budgetConfig) {
            self::calculationAmount($budgetConfig);
        }
    }

    /**
     * @param int $id
     * @return BudgetConfig
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function copy(int $id): BudgetConfig
    {
        $model = $this->findCurrentOne($id);
        $budgetConfig = new BudgetConfig();
        $values = $model->toArray();
        $budgetConfig->load($values, '');
        $budgetConfig->name = $budgetConfig->name . ' Copy';
        if (!$budgetConfig->save(false)) {
            throw new Exception(Setup::errorMessage($budgetConfig->firstErrors));
        }
        return BudgetConfig::findOne($budgetConfig->id);
    }

    /**
     * @param int $id
     * @return BudgetConfig
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): BudgetConfig
    {
        if (!$model = BudgetConfig::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }
}
