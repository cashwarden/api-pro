<?php

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Account;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\Record;
use app\core\models\Rule;
use app\core\models\Tag;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\types\ColorType;
use app\core\types\LedgerType;
use app\core\types\TransactionType;
use Yii;
use yii\db\Exception as DBException;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class FixDataService
{
    /**
     * 修复历史数据账本问题
     * @throws \yii\db\Exception|InvalidArgumentException
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
                Rule::updateAll(['ledger_id' => $model->id], ['user_id' => $userId, 'ledger_id' => null]);
                $transaction->commit();
            } catch (\Exception $e) {
                Yii::error('修复历史数据账本失败', (string)$e);
                $transaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * @throws DBException
     */
    public static function fixLedgerCategory()
    {
        $ledgers = Ledger::find()->asArray()->all();
        foreach ($ledgers as $ledger) {
            $category = Category::find()
                ->where(['ledger_id' => $ledger['id'], 'transaction_type' => TransactionType::ADJUST])
                ->exists();
            if ($category) {
                continue;
            }
            $items = [
                [
                    'name' => Yii::t('app', 'Other expenses'),
                    'color' => ColorType::GEEK_BLUE,
                    'icon_name' => 'expenses',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Account::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Other income'),
                    'color' => ColorType::MAGENTA,
                    'icon_name' => 'income',
                    'transaction_type' => TransactionType::INCOME,
                    'default' => Category::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Transfer'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'transfer',
                    'transaction_type' => TransactionType::TRANSFER,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Adjust Balance'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'adjust',
                    'transaction_type' => TransactionType::ADJUST,
                    'default' => Category::NOT_DEFAULT,
                ],
            ];
            $time = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($items as $key => $value) {
                $rows[$key] = $value;
                $rows[$key]['user_id'] = $ledger['user_id'];
                $rows[$key]['ledger_id'] = $ledger['id'];
                $rows[$key]['created_at'] = $time;
                $rows[$key]['updated_at'] = $time;
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
        }
    }
}
