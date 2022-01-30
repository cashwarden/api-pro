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

use app\core\helpers\DateHelper;
use app\core\models\AuthClient;
use app\core\models\Category;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\AuthClientType;
use app\core\types\DirectionType;
use app\core\types\TelegramAction;
use app\core\types\TelegramKeyword;
use app\core\types\TransactionRating;
use app\core\types\TransactionType;
use Carbon\Carbon;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yiier\graylog\Log;
use yiier\helpers\Setup;
use yiier\helpers\StringHelper;

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
     * @param Client $bot
     */
    public function bind(Client $bot): void
    {
        $bot->on(function (Update $Update) use ($bot) {
            $message = $Update->getMessage();
            $token = StringHelper::after(TelegramKeyword::BIND . '/', $message->getText());
            try {
                $user = $this->userService->getUserByResetToken($token);
                $expand = [
                    'client_username' => $message->getFrom()->getUsername() ?: $message->getFrom()->getFirstName(),
                    'client_id' => (string) $message->getFrom()->getId(),
                    'data' => $message->toJson(),
                ];
                UserService::findOrCreateAuthClient($user->id, AuthClientType::TELEGRAM, $expand);
                User::updateAll(['password_reset_token' => null], ['id' => $user->id]);

                $text = '成功绑定账号【' . data_get($user, 'username') . '】！，发送文字直接记账，示例：「买菜2」';
            } catch (\Exception $e) {
                $text = $e->getMessage();
            }
            /** @var BotApi $bot */
            $bot->sendMessage($message->getChat()->getId(), $text);
        }, function (Update $message) {
            $msg = $message->getMessage();
            if ($msg && strpos($msg->getText(), TelegramKeyword::BIND) === 0) {
                return true;
            }
            return false;
        });
    }


    /**
     * @param Transaction $transaction
     * @param int $page
     * @return string
     * @throws InvalidConfigException
     */
    public function getRecordsTextByTransaction(Transaction $transaction, int $page = 0): string
    {
        $limit = 10;
        if (strpos($transaction->description, '今天') !== false) {
            $transaction->date = TransactionService::getCreateRecordDate();
        }
        if ((!$transaction->ledger_id) || !($transaction->category_id || $transaction->date)) {
            return '未匹配到';
        }
        $query = Record::find()
            ->where(['ledger_id' => $transaction->ledger_id])
            ->limit($limit)
            ->offset($page * $limit)
            ->orderBy(['date' => SORT_DESC, 'id' => SORT_DESC]);

        if ($transaction->date) {
            $t = Carbon::parse($transaction->date);
            $dateRange = [
                $t->toDateString(),
                $t->endOfDay()->toDateTimeString(),
            ];
            $query->andWhere(['between', 'date', $dateRange[0], $dateRange[1]]);
        } else {
            $query->andFilterWhere(['category_id' => $transaction->category_id]);
        }

        $records = $query->all();
        if (!count($records)) {
            return '没有数据';
        }
        $text = '';
        if ($transaction->date) {
            $date = DateHelper::toDate($transaction->date);
            $text = "[{$date}] 的最近交易明细\n";
        } elseif ($transaction->category_id) {
            $categoryName = Category::find()->select('name')->where(['id' => $transaction->category_id])->scalar();
            $text = "[{$categoryName}] 分类最近交易明细\n";
        }

        $text .= '交易时间|分类|账户|金额' . "\n";
        $categoryMap = CategoryService::getMapByLedgerId($transaction->ledger_id);
        /** @var Record $record */
        foreach ($records as $record) {
            $text .= DateHelper::toDateTime($record->date, 'php:m-d H:i') . '|';
            $text .= $categoryMap[$record->category_id] . '|';
            $text .= $record->account->name . '|';
            $text .= $record->direction == DirectionType::EXPENSE ? '-' : '';
            $text .= Setup::toYuan($record->amount_cent) . '|';
            $transaction = $record->transaction;
            $remark = $transaction->remark ? "（{$transaction->remark}）" : '';
            $text .= "{$transaction->description}$remark\n";
        }

        return $text;
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
                    'action' => TelegramAction::TRANSACTION_DELETE,
                    'id' => $model->id,
                ]),
            ],
            [
                'text' => '😍' . $tests[TransactionRating::MUST] . $rating[TransactionRating::MUST],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::MUST,
                ]),
            ],
            [
                'text' => '😐' . $tests[TransactionRating::NEED] . $rating[TransactionRating::NEED],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::NEED,
                ]),
            ],
            [
                'text' => '💩' . $tests[TransactionRating::WANT] . $rating[TransactionRating::WANT],
                'callback_data' => Json::encode([
                    'action' => TelegramAction::TRANSACTION_RATING,
                    'id' => $model->id,
                    'value' => TransactionRating::WANT,
                ]),
            ],
        ];

        return new InlineKeyboardMarkup([$items]);
    }

    /**
     * @param string $messageText
     * @param int|null $chatId
     * @param null $keyboard
     * @param int $userId
     */
    public function sendMessage(string $messageText, int $chatId = null, $keyboard = null, int $userId = 0): void
    {
        $userId = $userId ?: Yii::$app->user->id;
        if (!$chatId) {
            $telegram = AuthClient::find()->select('data')->where([
                'user_id' => $userId,
                'type' => AuthClientType::TELEGRAM,
            ])->scalar();
            if (!$telegram) {
                return;
            }
            $telegram = Json::decode($telegram);
            $chatId = $telegram['chat']['id'];
            if (!$chatId) {
                return;
            }
        }

        $bot = self::newClient();
        // 重试五次
        for ($i = 0; $i < 5; $i++) {
            /** @var BotApi $bot */
            try {
                $bot->sendMessage($chatId, $messageText, null, false, null, $keyboard);
                break;
            } catch (Exception $e) {
                Log::error('发送 telegram 消息失败', [$messageText, (string) $e]);
            }
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

    public function getMessageTextByRecord(Record $record, string $title = '余额调整成功'): string
    {
        $text = "{$title}\n";
        $text .= "时间： {$record->date}\n";
        $accountBalance = Setup::toYuan($record->account->balance_cent);
        $text .= "账户： #{$record->account->name} （余额：{$accountBalance}）\n";
        $direction = $record->direction == DirectionType::INCOME ? '+' : '-';
        $text .= "金额：{$direction}" . Setup::toYuan($record->amount_cent);
        return $text;
    }

    /**
     * @param int $userId
     * @param string $type
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
        $recordOverview = $this->analysisService->getRecordOverview([$type]);
        $date = AnalysisDateType::getDateByType($type);
        $recordByCategory = $this->analysisService->byCategory(['date' => implode('~', $date)]);
        $text = "收支报告\n";

        $title = data_get($recordOverview, "{$type}.text");
        $expense = data_get($recordOverview, "{$type}.overview.expense", 0);
        $income = data_get($recordOverview, "{$type}.overview.income", 0);
        $surplus = data_get($recordOverview, "{$type}.overview.surplus", 0);
        $text .= "{$title}统计：已支出 {$expense}，已收入 {$income}，结余 {$surplus}\n";
        foreach ($recordByCategory['expense'] as $item) {
            $text .= "    * {$item['category_name']}：- {$item['amount']}\n";
        }
        foreach ($recordByCategory['income'] as $item) {
            $text .= "    * {$item['category_name']}：+ {$item['amount']}\n";
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
                    'page' => $page,
                ]),
            ],
        ];

        return new InlineKeyboardMarkup([$items]);
    }

    public function getRecordMarkup(Record $record): InlineKeyboardMarkup
    {
        $items = [
            [
                'text' => '🚮删除',
                'callback_data' => Json::encode([
                    'action' => TelegramAction::RECORD_DELETE,
                    'id' => $record->id,
                ]),
            ],
            [
                'text' => '占位',
                'callback_data' => '占位',
            ],
            [
                'text' => '占位',
                'callback_data' => '占位',
            ],
            [
                'text' => '占位',
                'callback_data' => '占位',
            ],
        ];

        return new InlineKeyboardMarkup([$items]);
    }

    public function messageCallback(Client $bot)
    {
        $bot->callbackQuery(function (CallbackQuery $message) use ($bot) {
            $user = $this->userService->getUserByClientId(
                AuthClientType::TELEGRAM,
                $message->getFrom()->getId()
            );
            if ($user) {
                \Yii::$app->user->setIdentity($user);
                /** @var BotApi $bot */
                if (!$data = json_decode($message->getData(), true)) {
                    Log::warning('telegram callback error', $message->getData());
                    return;
                }
                $bot->answerCallbackQuery($message->getId(), 'Loading...');
                switch (data_get($data, 'action')) {
                    case TelegramAction::TRANSACTION_DELETE:
                        $this->transactionDelete($message, $bot, $data);
                        break;
                    case TelegramAction::RECORD_DELETE:
                        $this->recordDelete($message, $bot, $data);
                        break;
                    case TelegramAction::FIND_CATEGORY_RECORDS:
                        $this->findCategoryRecords($message, $bot, $data);
                        break;
                    case TelegramAction::TRANSACTION_RATING:
                        $this->transactionRating($message, $bot, $data);
                        break;
                    default:
                        // code...
                        break;
                }
            }
        });
    }

    private function recordDelete(CallbackQuery $message, Client $bot, array $data)
    {
        $chatId = $message->getMessage()->getChat()->getId();
        /** @var BotApi $bot */
        /** @var Record $model */
        if ($model = Record::find()->where(['id' => data_get($data, 'id')])->one()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $model->delete();
                $text = '记录成功被删除';
                $transaction->commit();
                $bot->editMessageText($chatId, $message->getMessage()->getMessageId(), $text);
            } catch (Throwable $e) {
                $transaction->rollBack();
                Log::error('删除记录失败', ['model' => $model->attributes, 'e' => (string) $e]);
            }
        } else {
            $text = '删除失败，记录已被删除或者不存在';
            $replyToMessageId = $message->getMessage()->getMessageId();
            $bot->sendMessage($chatId, $text, null, false, $replyToMessageId);
        }
    }

    private function transactionDelete(CallbackQuery $message, Client $bot, array $data)
    {
        $chatId = $message->getMessage()->getChat()->getId();
        /** @var BotApi $bot */
        /** @var Transaction $model */
        if ($model = Transaction::find()->where(['id' => data_get($data, 'id')])->one()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                foreach ($model->records as $record) {
                    $record->delete();
                }
                $text = '记录成功被删除';
                $transaction->commit();
                $bot->editMessageText($chatId, $message->getMessage()->getMessageId(), $text);
            } catch (Throwable $e) {
                $transaction->rollBack();
                Log::error(
                    '删除记录失败',
                    ['message' => $message->toJson(), 'model' => $model->attributes, 'e' => (string) $e]
                );
            }
        } else {
            $text = '删除失败，记录已被删除或者不存在';
            $replyToMessageId = $message->getMessage()->getMessageId();
            $bot->sendMessage($chatId, $text, null, false, $replyToMessageId);
        }
    }

    private function findCategoryRecords(CallbackQuery $message, Client $bot, array $data)
    {
        /** @var BotApi $bot */
        $transaction = new Transaction();
        $transaction->load($data, '');
        $page = data_get($data, 'page', 0) + 1;
        $text = $this->getRecordsTextByTransaction($transaction, $page);
        $keyboard = $this->getRecordsMarkup($transaction, $page);
        $replyToMessageId = $message->getMessage()->getMessageId();
        $chatId = $message->getMessage()->getChat()->getId();
        $bot->sendMessage($chatId, $text, null, false, $replyToMessageId, $keyboard);
    }

    private function transactionRating(CallbackQuery $message, Client $bot, array $data)
    {
        $chatId = $message->getMessage()->getChat()->getId();
        /** @var BotApi $bot */
        $id = data_get($data, 'id');
        if ($this->transactionService->updateRating($id, data_get($data, 'value'))) {
            $replyMarkup = $this->getTransactionMarkup(Transaction::findOne($id));
            $bot->editMessageReplyMarkup($chatId, $message->getMessage()->getMessageId(), $replyMarkup);
        } else {
            $text = '评分失败，记录已被删除或者不存在';
            $replyToMessageId = $message->getMessage()->getMessageId();
            $bot->sendMessage($chatId, $text, null, false, $replyToMessageId);
        }
    }

    public function reportMessage(Client $bot)
    {
        $bot->on(function (Update $Update) use ($bot) {
            $message = $Update->getMessage();
            $user = $this->userService->getUserByClientId(
                AuthClientType::TELEGRAM,
                $message->getFrom()->getId()
            );
            if ($user) {
                \Yii::$app->user->setIdentity($user);
                $type = ltrim($message->getText(), '/');
                $text = $this->telegramService->getReportTextByType($type);
            } else {
                $text = '请先绑定您的账号';
            }
            /** @var BotApi $bot */
            $bot->sendMessage($message->getChat()->getId(), $text);
        }, function (Update $message) {
            $msg = $message->getMessage();
            $report = [
                TelegramKeyword::TODAY,
                TelegramKeyword::YESTERDAY,
                TelegramKeyword::LAST_MONTH,
                TelegramKeyword::CURRENT_MONTH,
            ];
            if ($msg && in_array($msg->getText(), $report)) {
                return true;
            }
            return false;
        });
    }

    public function passwordReset(Client $bot)
    {
        $bot->command(ltrim(TelegramKeyword::PASSWORD_RESET, '/'), function (Message $message) use ($bot) {
            $text = '您还未绑定账号，请先访问「个人设置」中的「账号绑定」进行绑定账号，然后才能使用此功能。';
            $user = $this->userService->getUserByClientId(
                AuthClientType::TELEGRAM,
                $message->getFrom()->getId()
            );
            if ($user) {
                (new UserService())->setPasswordResetToken($user);
                $resetURL = params('frontendURL') .
                    '#/passport/password-reset?token=' .
                    $user->password_reset_token;
                $text = "请在 24 小时内使使用此链接设置新密码\n {$resetURL}";
            }
            /** @var BotApi $bot */
            $bot->sendMessage($message->getChat()->getId(), $text);
        });
    }

    public function start(Client $bot)
    {
        $bot->command(ltrim(TelegramKeyword::START, '/'), function (Message $message) use ($bot) {
            $text = '您还未绑定账号，请先访问「个人设置」中的「账号绑定」进行绑定账号，然后才能快速记账。';
            $user = $this->userService->getUserByClientId(
                AuthClientType::TELEGRAM,
                $message->getFrom()->getId()
            );
            if ($user) {
                $text = '欢迎回来👏，发送文字直接记账';
            }
            /** @var BotApi $bot */
            $bot->sendMessage($message->getChat()->getId(), $text);
        });
    }

    /**
     * @throws Exception
     * @throws \TelegramBot\Api\HttpException
     * @throws \TelegramBot\Api\InvalidJsonException
     */
    public static function setMyCommands()
    {
        /** @var BotApi $bot */
        $bot = self::newClient();
        $commands = [];
        foreach (TelegramKeyword::commands() as $key => $value) {
            array_push($commands, ['command' => $key, 'description' => $value]);
        }
        $bot->setMyCommands($commands);
    }
}
