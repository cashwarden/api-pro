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

use app\core\models\Account;
use app\core\models\Category;
use app\core\types\TransactionType;
use yii\helpers\ArrayHelper;

class CategoryService
{
    public static function getDefaultCategory(int $transactionType, int $ledgerId): array
    {
        $userIds = UserService::getCurrentMemberIds();
        return Category::find()
            ->where(['user_id' => $userIds, 'transaction_type' => $transactionType, 'ledger_id' => $ledgerId])
            ->orderBy(['default' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->one();
    }

    public static function getAdjustCategoryId(): int
    {
        $ledgerId = data_get(LedgerService::getDefaultLedger(), 'id');
        $userIds = UserService::getCurrentMemberIds();
        return Category::find()
            ->where(['user_id' => $userIds, 'transaction_type' => TransactionType::ADJUST])
            ->andFilterWhere(['ledger_id' => $ledgerId])
            ->orderBy(['id' => SORT_ASC])
            ->scalar();
    }

    /**
     * @param  int  $ledgerId
     * @return array
     */
    public static function getMapByLedgerId(int $ledgerId): array
    {
        $userIds = UserService::getCurrentMemberIds();
        $categories = Category::find()->where(['user_id' => $userIds, 'ledger_id' => $ledgerId])->asArray()->all();
        return ArrayHelper::map($categories, 'id', 'name');
    }

    /**
     * @param  string  $desc
     * @param  int  $ledgerId
     * @param  int  $transactionType
     * @return int
     */
    public function getCategoryIdByDesc(string $desc, int $ledgerId, int $transactionType)
    {
        $models = Category::find()
            ->where([
                'user_id' => UserService::getCurrentMemberIds(),
                'ledger_id' => $ledgerId,
                'transaction_type' => $transactionType,
            ])
            ->andWhere(['<>', 'keywords', ''])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])
            ->all();
        /** @var Account $model */
        foreach ($models as $model) {
            if (\app\core\helpers\ArrayHelper::strPosArr($desc, explode(',', $model->keywords)) !== false) {
                return $model->id;
            }
        }
        return 0;
    }
}
