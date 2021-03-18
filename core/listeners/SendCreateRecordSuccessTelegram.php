<?php

namespace app\core\listeners;

use app\core\models\Record;
use app\core\models\Transaction;
use app\core\traits\ServiceTrait;
use Guanguans\YiiEvent\ListenerInterface;
use yii\base\Event;

class SendCreateRecordSuccessTelegram implements ListenerInterface
{
    use ServiceTrait;

    public function handle(Event $event)
    {
        $text = '内部错误';
        $keyboard = null;
        $data = $event->data;
        if ($data instanceof Transaction) {
            $keyboard = $this->getTelegramService()->getTransactionMarkup($data);
            $text = $this->getTelegramService()->getMessageTextByTransaction($data);
        } elseif ($data instanceof Record) {
            $keyboard = $this->getTelegramService()->getRecordMarkup($data);
            $text = $this->getTelegramService()->getMessageTextByRecord($data);
        }
        $this->getTelegramService()->sendMessage($text, $keyboard, $data->user_id);
    }
}
