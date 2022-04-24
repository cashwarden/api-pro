<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\models;

/**
 * Class Search.
 * @property int $id
 * @property int $ledger_id
 * @property int $user_id
 * @property int $category_id
 * @property int $date
 * @property string $tags
 * @property string $content
 */
class Search extends \hightman\xunsearch\ActiveRecord
{
    public static function createUpdate(bool $insert, Transaction $transaction): bool
    {
        if ($insert) {
            $search = new self();
            $search->id = $transaction->id;
        } else {
            $search = self::findOne($transaction->id);
            if (!$search) {
                // 如果立即修改 会因为在 xunsearch 找不到而不能 save
                return false;
            }
        }
        $search->tags = is_array($transaction->tags) ? implode(',', $transaction->tags) : $transaction->tags;
        $search->content = implode([$transaction->description, $transaction->remark]);
        $search->date = strtotime($transaction->date);
        $search->user_id = $transaction->user_id;
        $search->category_id = $transaction->category_id;
        $search->ledger_id = $transaction->ledger_id;
        $save = $search->save();
        $search::getDb()->getIndex()->flushIndex();
        return $save;
    }
}
