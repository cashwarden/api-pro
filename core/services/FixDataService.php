<?php

namespace app\core\services;

use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\Record;
use app\core\models\Rule;
use app\core\models\Tag;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\types\LedgerType;
use Yii;
use yiier\helpers\Setup;

class FixDataService
{
    /**
     * 修复历史数据账本问题
     * @throws \yii\db\Exception
     */
    public static function initLedger()
    {
        $userIds = User::find()->asArray()->column();
        foreach ($userIds as $userId) {
            if (Ledger::find()->where(['user_id' => $userId])->exists()) {
                continue;
            }
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model = new Ledger();
                $model->name = '日常生活';
                $model->type = LedgerType::getName(LedgerType::GENERAL);
                $model->user_id = $userId;
                $model->default = true;
                if (!$model->save()) {
                    throw new \Exception(Setup::errorMessage($model->firstErrors));
                }
                Category::updateAll(['ledger_id' => $model->id], ['user_id' => $userId, 'ledger_id' => null]);
                Transaction::updateAll(['ledger_id' => $model->id], ['user_id' => $userId, 'ledger_id' => null]);
                Record::updateAll(['ledger_id' => $model->id], ['user_id' => $userId, 'ledger_id' => null]);
                Tag::updateAll(['ledger_id' => $model->id], ['user_id' => $userId, 'ledger_id' => null]);
                Rule::updateAll(['then_ledger_id' => $model->id], ['user_id' => $userId, 'then_ledger_id' => null]);
                $transaction->commit();
            } catch (\Exception $e) {
                Yii::error('修复历史数据账本失败', (string)$e);
                $transaction->rollBack();
                throw $e;
            }
        }
    }
}
