<?php

namespace app\core\helpers;

use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\traits\SendRequestTrait;
use yiier\graylog\Log;

class HolidayHelper
{
    use SendRequestTrait;

    private static $times = 0;

    /**
     * @return mixed
     * @throws ThirdPartyServiceErrorException
     */
    public static function getNextWorkday()
    {
        $baseUrl = 'http://timor.tech/api/holiday/workday/next';
        /** @var HolidayHelper $self */

        try {
            $self = \Yii::createObject(self::class);
            $response = $self->sendRequest('GET', $baseUrl);
            $data = json_decode($response);
            if ($data->code == 0) {
                return $data->workday->date;
            }
        } catch (\Throwable $e) {
            self::$times++;
            Log::error('holiday-error', [$response ?? [], self::$times, (string)$e]);
            if (self::$times > 3) {
                throw new ThirdPartyServiceErrorException();
            }
            sleep(10);
            return self::getNextWorkday();
        }
    }
}
