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

use app\core\models\Tag;
use app\core\models\Transaction;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yiier\helpers\Setup;

class TagService
{
    public static function getTagNames(int $ledgerId): array
    {
        return Tag::find()->select('name')->where(['ledger_id' => $ledgerId])->column();
    }

    /**
     * @param  array  $data
     * @return Tag
     * @throws Exception
     */
    public function create(array $data)
    {
        $model = new Tag();
        $model->load($data, '');
        $model->user_id = Yii::$app->user->id;
        if (!$model->save(false)) {
            throw new Exception(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param  array  $tags
     * @param  int  $ledgerId
     * @throws \yii\base\InvalidConfigException
     */
    public static function updateCounters(array $tags, int $ledgerId)
    {
        $userIds = UserService::getCurrentMemberIds();
        foreach ($tags as $tag) {
            $count = TransactionService::countTransactionByTag($tag, $ledgerId, $userIds);
            Tag::updateAll(
                ['count' => $count, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['ledger_id' => $ledgerId, 'user_id' => $userIds, 'name' => $tag]
            );
        }
    }

    /**
     * @param  string  $oldName
     * @param  string  $newName
     * @param  int  $ledgerId
     * @throws \yii\base\InvalidConfigException
     */
    public static function updateTagName(string $oldName, string $newName, int $ledgerId)
    {
        $userIds = UserService::getCurrentMemberIds();
        $items = Transaction::find()
            ->where(['user_id' => $userIds, 'ledger_id' => $ledgerId])
            ->andWhere(new Expression('FIND_IN_SET(:tag, tags)'))->addParams([':tag' => $oldName])
            ->asArray()
            ->all();
        $ids = [];
        /** @var Transaction $item */
        foreach ($items as $item) {
            $tags = str_replace($oldName, $newName, $item['tags']);
            Transaction::updateAll(
                ['tags' => $tags, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                ['id' => $item['id']]
            );
            array_push($ids, $item['id']);
        }
        TransactionService::updateXunSearch($ids);
    }
}
