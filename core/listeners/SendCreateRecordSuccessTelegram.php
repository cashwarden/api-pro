<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\listeners;

use app\core\models\Record;
use app\core\models\Transaction;
use app\core\traits\ServiceTrait;
use Guanguans\YiiEvent\ListenerInterface;
use yii\base\Event;

class SendCreateRecordSuccessTelegram implements ListenerInterface
{
    use ServiceTrait;

    /**
     * @param Event $event
     * @throws \Exception
     */
    public function handle(Event $event): void
    {
        $text = '内部错误';
        $keyboard = null;
        $model = data_get($event->data, 'model');
        $chatId = data_get($event->data, 'chat_id');
        if ($model instanceof Transaction) {
            $keyboard = $this->getTelegramService()->getTransactionMarkup($model);
            $text = $this->getTelegramService()->getMessageTextByTransaction($model);
        } elseif ($model instanceof Record) {
            $keyboard = $this->getTelegramService()->getRecordMarkup($model);
            $text = $this->getTelegramService()->getMessageTextByRecord($model);
        }
        $this->getTelegramService()->sendMessage($text, $chatId, $keyboard, $model->user_id);
    }
}
