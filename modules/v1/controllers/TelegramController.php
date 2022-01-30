<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\helpers\ArrayHelper;
use app\core\services\TelegramService;
use app\core\traits\ServiceTrait;
use app\core\types\AuthClientType;
use app\core\types\TelegramKeyword;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use yiier\graylog\Log;

class TelegramController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public array $noAuthActions = ['hook', 'bind'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @throws \Exception
     */
    public function actionHook()
    {
        try {
            $bot = TelegramService::newClient();

            // 记账记录按钮操作
            $this->telegramService->messageCallback($bot);
            $this->telegramService->passwordReset($bot);
            $this->telegramService->start($bot);

            $bot->command('ping', function (Message $message) use ($bot) {
                $keyboard = new ReplyKeyboardMarkup(
                    [['one', 'two', 'three']],
                    true
                ); // true for one-time keyboard
                /** @var BotApi $bot */
                $bot->sendMessage($message->getChat()->getId(), 'pong!', null, false, null, $keyboard);
            });

            $this->telegramService->reportMessage($bot);
            $this->telegramService->bind($bot);

            // 查询 && 记账
            $bot->on(function (Update $Update) use ($bot) {
                $message = $Update->getMessage();
                $keyboard = null;
                $text = '';
                $chatId = $message->getChat()->getId();
                try {
                    $user = $this->userService->getUserByClientId(
                        AuthClientType::TELEGRAM,
                        $message->getFrom()->getId()
                    );
                    \Yii::$app->user->setIdentity($user);
                    $t = $message->getText();
                    if (strpos($t, '@') !== false) {
                        $model = $this->transactionService->createBaseTransactionByDesc($t);
                        $keyboard = $this->telegramService->getRecordsMarkup($model);
                        $text = $this->telegramService->getRecordsTextByTransaction($model);
                    } else {
                        $this->transactionService->createByDesc($t, $chatId);
                    }
                } catch (\Exception $e) {
                    $text = $e->getMessage();
                }
                if ($text) {
                    /** @var BotApi $bot */
                    $bot->sendMessage($chatId, $text, null, false, null, $keyboard);
                }
            }, function (Update $message) {
                if ($message->getMessage()) {
                    if (ArrayHelper::strPosArr($message->getMessage()->getText(), TelegramKeyword::items()) === 0) {
                        return false;
                    }
                    return true;
                }
                return false;
            });

            $bot->run();
        } catch (Exception $e) {
            Log::error('webHook error' . $e->getMessage(), (string) $e);
            throw $e;
        }
    }
}
