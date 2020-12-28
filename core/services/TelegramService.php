<?php

namespace app\core\services;

use app\core\helpers\DateHelper;
use app\core\models\AuthClient;
use app\core\models\Category;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\AuthClientStatus;
use app\core\types\AuthClientType;
use app\core\types\ReportType;
use app\core\types\TelegramAction;
use app\core\types\TransactionRating;
use app\core\types\TransactionType;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\db\Exception as DBException;
use yii\helpers\Json;
use yiier\graylog\Log;
use yiier\helpers\Setup;

class TelegramService extends BaseObject
{
    use ServiceTrait;

    /**
     * @return Client|object
     */
    public static function newClient()
    {
        try {
            return Yii::createObject(Client::class, [params('telegramToken')]);
        } catch (InvalidConfigException $e) {
            return new Client(params('telegramToken'));
        }
    }

    /**
     * @param User $user
     * @param string $token
     * @param Message $message
     * @throws DBException
     */
    public function bind(User $user, string $token, Message $message): void
    {
        Yii::error($message, 'telegram_message' . $token);

        $conditions = [
            'type' => AuthClientType::TELEGRAM,
            'user_id' => $user->id,
            'status' => AuthClientStatus::ACTIVE
        ];
        if (!$model = AuthClient::find()->where($conditions)->one()) {
            $model = new AuthClient();
            $model->load($conditions, '');
        }
        $model->client_username = (string)($message->getFrom()->getUsername() ?: $message->getFrom()->getFirstName());
        $model->client_id = (string)$message->getFrom()->getId();
        $model->data = $message->toJson();
        if (!$model->save()) {
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
        User::updateAll(['password_reset_token' => null], ['id' => $user->id]);
    }


    /**
     * @param Transaction $transaction
     * @param int $page
     * @return string
     * @throws InvalidArgumentException|InvalidConfigException
     */
    public function getRecordsTextByTransaction(Transaction $transaction, $page = 0): string
    {
        $limit = 10;
        if ((!$transaction->date) || (!$transaction->ledger_id)) {
            throw new InvalidArgumentException('未匹配到时间');
        } elseif ((!$transaction->category_id) || (!$transaction->ledger_id)) {
            throw new InvalidArgumentException('未匹配到分类');
        }
        $records = Record::find()
            ->where(['ledger_id' => $transaction->ledger_id])
            ->andWhere(['category_id' => $transaction->category_id])
            ->limit($limit)
            ->offset($page * $limit)
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC])
            ->all();
        if (!count($records)) {
            return '没有数据';
        }
        $text = '';
        if ($transaction->date) {
            $date = DateHelper::toDateTime($transaction->date);
            $text = "[{$date}的最近交易明细\n";
        } elseif ($transaction->category_id) {
            $categoryName = Category::find()->select('name')->where(['id' => $transaction->category_id])->scalar();
            $text = "[{$categoryName}]分类最近交易明细\n";
        }

        $text .= '交易时间|分类|交易类型|账户|金额' . "\n";
        $types = TransactionType::texts();
        $categoryMap = CategoryService::getMapByLedgerId($transaction->ledger_id);
        /** @var Record $record */
        foreach ($records as $record) {
            $text .= DateHelper::toDateTime($record->date, 'php:m-d H:i') . '|';
            $text .= $categoryMap[$record->category_id] . '|';
            $text .= $types[$record->transaction_type] . '|';
            $text .= $record->account->name . '|';
            $text .= Setup::toYuan($record->amount_cent) . "\n";
        }

        return $text;
    }

    /**
     * @param CallbackQuery $message
     * @param Client $bot
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function callbackQuery(CallbackQuery $message, Client $bot)
    {
        /** @var BotApi $bot */
        $data = Json::decode($message->getData());
        switch (data_get($data, 'action')) {
            case TelegramAction::RECORD_DELETE:
                /** @var Transaction $model */
                if ($model = Transaction::find()->where(['id' => data_get($data, 'id')])->one()) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        foreach ($model->records as $record) {
                            $record->delete();
                        }
                        $text = '记录成功被删除';
                        $transaction->commit();
                        $bot->editMessageText(
                            $message->getFrom()->getId(),
                            $message->getMessage()->getMessageId(),
                            $text
                        );
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Log::error('删除记录失败', ['model' => $model->attributes, 'e' => (string)$e]);
                    }
                } else {
                    $text = '删除失败，记录已被删除或者不存在';
                    $replyToMessageId = $message->getMessage()->getMessageId();
                    $bot->sendMessage($message->getFrom()->getId(), $text, null, false, $replyToMessageId);
                }

                break;
            case TelegramAction::FIND_CATEGORY_RECORDS:
                $transaction = new Transaction();
                $transaction->load($data, '');
                $page = data_get($data, 'page', 0) + 1;
                $text = $this->getRecordsTextByTransaction($transaction, $page);
                $keyboard = $this->getRecordsMarkup($transaction, $page);
                $replyToMessageId = $message->getMessage()->getMessageId();
                $bot->sendMessage($message->getFrom()->getId(), $text, null, false, $replyToMessageId, $keyboard);

                break;
            case TelegramAction::TRANSACTION_RATING:
                $id = data_get($data, 'id');
                if ($this->transactionService->updateRating($id, data_get($data, 'value'))) {
                    $replyMarkup = $this->getTransactionMarkup(Transaction::findOne($id));
                    $bot->editMessageReplyMarkup(
                        $message->getFrom()->getId(),
                        $message->getMessage()->getMessageId(),
                        $replyMarkup
                    );
                } else {
                    $text = '评分失败，记录已被删除或者不存在';
                    $replyToMessageId = $message->getMessage()->getMessageId();
                    $bot->sendMessage($message->getFrom()->getId(), $text, null, false, $replyToMessageId);
                }

                break;
            default:
                # code...
                break;
        }
    }

    public function getTransactionMarkup(Transaction $model): InlineKeyboardMarkup
    {
        $tests = TransactionRating::texts();
        $rating = [];
        foreach (TransactionRating::names() as $key => $name) {
            $rating[$key] = null;
        }
        if ($model->rating) {
            $rating[$model->rating] = 1;
        }
        $items = [
            [
                'text' => '🚮删除',
                'callback_data' => Json::encode([
                    'action' => TelegramAction::RECORD_DELETE,
                    'id' => $model->id
                ]),
            ],
            [
                'text' => '😍' . $tests[TransactionRating::MUST] . $rating[TransactionRating::MUST],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::MUST
                ]),
            ],
            [
                'text' => '😐' . $tests[TransactionRating::NEED] . $rating[TransactionRating::NEED],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::NEED
                ]),
            ],
            [
                'text' => '💩' . $tests[TransactionRating::WANT] . $rating[TransactionRating::WANT],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::WANT
                ]),
            ]
        ];

        return new InlineKeyboardMarkup([$items]);
    }

    /**
     * @param string $messageText
     * @param null $keyboard
     * @param int $userId
     * @return void
     */
    public function sendMessage(string $messageText, $keyboard = null, int $userId = 0): void
    {
        $userId = $userId ?: Yii::$app->user->id;
        $telegram = AuthClient::find()->select('data')->where([
            'user_id' => $userId,
            'type' => AuthClientType::TELEGRAM
        ])->scalar();
        if (!$telegram) {
            return;
        }
        $telegram = Json::decode($telegram);
        if (empty($telegram['chat']['id'])) {
            return;
        }
        $bot = TelegramService::newClient();
        /** @var BotApi $bot */
        try {
            $bot->sendMessage($telegram['chat']['id'], $messageText, null, false, null, $keyboard);
        } catch (InvalidArgumentException $e) {
        } catch (Exception $e) {
        }
    }


    public function getMessageTextByTransaction(Transaction $model, string $title = '记账成功'): string
    {
        $text = "{$title}\n";
        $text .= '交易类目： #' . $model->category->name . "\n";
        $text .= '交易类型： #' . TransactionType::texts()[$model->type] . "\n";
        $text .= "交易时间： {$model->date}\n"; // todo add tag
        if (in_array($model->type, [TransactionType::EXPENSE, TransactionType::TRANSFER])) {
            $fromAccountName = $model->fromAccount->name;
            $fromAccountBalance = Setup::toYuan($model->fromAccount->balance_cent);
            $text .= "支付账户： #{$fromAccountName} （余额：{$fromAccountBalance}）\n";
        }
        if (in_array($model->type, [TransactionType::INCOME, TransactionType::TRANSFER])) {
            $toAccountName = $model->toAccount->name;
            $toAccountBalance = Setup::toYuan($model->toAccount->balance_cent);
            $text .= "收款账户： #{$toAccountName} （余额：{$toAccountBalance}）\n";
        }
        $text .= '金额：' . Setup::toYuan($model->amount_cent);
        return $text;
    }

    /**
     * @param int $userId
     * @param string $type
     * @return void
     * @throws \Exception
     */
    public function sendReport(int $userId, string $type): void
    {
        \Yii::$app->user->setIdentity(User::findOne($userId));
        $text = $this->telegramService->getReportTextByType($type);
        $this->telegramService->sendMessage($text);
    }

    /**
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function getReportTextByType(string $type): string
    {
        $recordOverview = $this->analysisService->recordOverview;
        $date = AnalysisDateType::getDateByType($type);
        $recordByCategory = $this->analysisService->byCategory(['date' => implode('~', $date)]);
        $text = "收支报告\n";

        $title = data_get($recordOverview, "{$type}.text");
        $expense = data_get($recordOverview, "{$type}.overview.expense", 0);
        $income = data_get($recordOverview, "{$type}.overview.income", 0);
        $surplus = data_get($recordOverview, "{$type}.overview.surplus", 0);
        $text .= "{$title}统计：已支出 {$expense}，已收入 {$income}，结余 {$surplus}\n";
        foreach ($recordByCategory['expense'] as $item) {
            $text .= "    - 【支出】{$item['category_name']}：{$item['currency_amount']}\n";
        }
        foreach ($recordByCategory['income'] as $item) {
            $text .= "    - 【收入】{$item['category_name']}：{$item['currency_amount']}\n";
        }

        $type = AnalysisDateType::CURRENT_MONTH;
        $title = data_get($recordOverview, "{$type}.text");
        $expense = data_get($recordOverview, "{$type}.overview.expense", 0);
        $income = data_get($recordOverview, "{$type}.overview.income", 0);
        $surplus = data_get($recordOverview, "{$type}.overview.surplus", 0);
        $text .= "{$title}统计：已支出 {$expense}，已收入 {$income}，结余 {$surplus}\n";

        $date = AnalysisDateType::getDateByType(AnalysisDateType::CURRENT_MONTH);
        $recordByCategory = $this->analysisService->byCategory(['date' => implode('~', $date)]);
        foreach ($recordByCategory['expense'] as $item) {
            $text .= "    - 【支出】{$item['category_name']}：{$item['currency_amount']}\n";
        }
        foreach ($recordByCategory['income'] as $item) {
            $text .= "    - 【收入】{$item['category_name']}：{$item['currency_amount']}\n";
        }

        return $text;
    }

    public function getRecordsMarkup(Transaction $model, int $page = 0): InlineKeyboardMarkup
    {
        $items = [
            [
                'text' => '查看更多',
                'callback_data' => Json::encode([
                    'action' => TelegramAction::FIND_CATEGORY_RECORDS,
                    'ledger_id' => $model->ledger_id,
                    'category_id' => $model->category_id,
                    'page' => $page
                ]),
            ],
        ];

        return new InlineKeyboardMarkup([$items]);
    }
}
