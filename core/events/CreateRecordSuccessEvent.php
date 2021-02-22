<?php

namespace app\core\events;

use yii\base\Event;

class CreateRecordSuccessEvent extends Event
{
    public $name = 'create-record-success';
}
