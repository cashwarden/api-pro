<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core;

use app\core\behaviors\LoggerBehavior;
use yii\base\BootstrapInterface;
use yii\base\Controller;
use yii\base\Event;
use yii\web\Response;

class EventBootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        Event::on(Response::class, Response::EVENT_BEFORE_SEND, function ($event) {
            \Yii::createObject(LoggerBehavior::class)->beforeSend($event);
        });

        Event::on(Controller::class, Controller::EVENT_BEFORE_ACTION, function ($event) {
            \Yii::createObject(LoggerBehavior::class)->beforeAction();
        });
    }
}
