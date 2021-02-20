<?php

namespace app\core\listeners;

use app\core\models\Record;
use app\core\models\User;
use app\core\traits\ServiceTrait;
use Guanguans\YiiEvent\ListenerInterface;
use yii\base\Event;

class SendCreateRecordSuccessTelegram implements ListenerInterface
{
    use ServiceTrait;

    public function handle(Event $event)
    {
        /** @var Record $record */
        $record = $event->data;
        $transaction = $record->transaction;
        \Yii::$app->user->switchIdentity(User::findOne($record->user_id));
        if ($transaction) {
            $keyboard = $this->getTelegramService()->getTransactionMarkup($record->transaction);
            $text = $this->getTelegramService()->getMessageTextByTransaction($transaction);
        } else {
            $keyboard = $this->getTelegramService()->getRecordMarkup($record);
            $text = $this->getTelegramService()->getMessageTextByRecord($record);
        }
        $this->getTelegramService()->sendMessage($text, $keyboard);
    }
}
