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

use app\core\models\Account;
use app\core\models\Category;
use app\core\types\TransactionType;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class CategoryService
{
    public static function getDefaultCategory(int $transactionType, int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Category::find()
            ->where(['user_id' => $userId, 'transaction_type' => $transactionType])
            ->orderBy(['default' => SORT_DESC, 'id' => SORT_ASC])
            ->asArray()
            ->one();
    }

    public static function getAdjustCategoryId(int $userId = 0)
    {
        $userId = $userId ?: Yii::$app->user->id;
        return Category::find()
            ->where(['user_id' => $userId, 'transaction_type' => TransactionType::ADJUST])
            ->orderBy(['id' => SORT_ASC])
            ->scalar();
    }

    /**
     * @param int $id
     * @return Account|ActiveRecord|null
     * @throws NotFoundHttpException
     */
    public static function findCurrentOne(int $id)
    {
        if (!$model = Category::find()->where(['id' => $id, 'user_id' => \Yii::$app->user->id])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }

    /**
     * @param int $userId
     * @return array
     */
    public static function getMapByUserId(int $userId = 0): array
    {
        $userId = $userId ?: Yii::$app->user->id;
        $categories = Category::find()->where(['user_id' => $userId])->asArray()->all();
        return ArrayHelper::map($categories, 'id', 'name');
    }

    /**
     * @param int $ledgerId
     * @return array
     */
    public static function getMapByLedgerId(int $ledgerId): array
    {
        $categories = Category::find()->where(['ledger_id' => $ledgerId])->asArray()->all();
        return ArrayHelper::map($categories, 'id', 'name');
    }

    /**
     * @param string $desc
     * @param int $ledgerId
     * @param int $transactionType
     * @return int
     */
    public function getCategoryIdByDesc(string $desc, int $ledgerId, int $transactionType)
    {
        $models = Category::find()
            ->where([
                'user_id' => \Yii::$app->user->id,
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
