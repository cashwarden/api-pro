<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\exceptions\InternalException;
use app\core\models\Account;
use app\core\models\Budget;
use app\core\models\BudgetConfig;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\Record;
use app\core\models\Recurrence;
use app\core\models\Rule;
use app\core\models\Tag;
use app\core\models\Transaction;
use app\core\models\WishList;
use app\core\types\ColorType;
use app\core\types\TransactionType;
use Yii;
use yii\db\Exception as DBException;
use yii\helpers\ArrayHelper;
use yiier\helpers\ModelHelper;

class LedgerService
{
    /**
     * @return array|\yii\db\ActiveRecord|null
     */
    public static function getDefaultLedger()
    {
        $userIds = UserService::getCurrentMemberIds();
        return Ledger::find()
            ->where(['user_id' => $userIds])
            ->orderBy(['default' => SORT_DESC, 'id' => SORT_ASC])
            ->one();
    }

    /**
     * @param  Ledger  $ledger
     * @return bool
     * @throws InternalException
     */
    public static function createLedgerAfter(Ledger $ledger): bool
    {
        try {
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
                $rows[$key]['user_id'] = $ledger->user_id;
                $rows[$key]['created_at'] = $time;
                $rows[$key]['updated_at'] = $time;
                $rows[$key]['ledger_id'] = $ledger->id;
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
        } catch (\Exception $e) {
            Yii::error('创建账本失败', [$ledger->attributes, (string) $e]);
            throw new InternalException($e->getMessage());
        }
        return true;
    }

    public static function afterDelete(int $ledgerId)
    {
        Category::deleteAll(['ledger_id' => $ledgerId]);
        $budgetConfigIds = BudgetConfig::find()->where(['ledger_id' => $ledgerId])->column();
        Budget::deleteAll(['budget_config_id' => $budgetConfigIds]);
        BudgetConfig::deleteAll(['ledger_id' => $ledgerId]);
        Record::deleteAll(['ledger_id' => $ledgerId]);
        Rule::deleteAll(['ledger_id' => $ledgerId]);
        Tag::deleteAll(['ledger_id' => $ledgerId]);
        WishList::deleteAll(['ledger_id' => $ledgerId]);
        $transactionIds = Transaction::find()->where(['ledger_id' => $ledgerId])->column();
        Recurrence::deleteAll(['transaction_id' => $transactionIds]);
        Transaction::deleteAll(['ledger_id' => $ledgerId]);
    }

    /**
     * @return array
     */
    public function getLedgersCategories(): array
    {
        $rows = [];
        /** @var Ledger[] $items */
        $items = Ledger::find()->where(['user_id' => UserService::getCurrentMemberIds()])->all();
        foreach ($items as $item) {
            $array = ArrayHelper::toArray($item->categories);
            $categories = ArrayHelper::index($array, null, 'transaction_type');
            $row = [
                'id' => $item->id,
                'name' => $item->name,
                'categories' => $categories,
            ];

            array_push($rows, $row);
        }
        return $rows;
    }
}
