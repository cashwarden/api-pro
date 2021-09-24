<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\CurrencyConverter;
use app\core\models\Currency;
use app\core\types\CurrencyType;
use yiier\graylog\Log;

/**
 * Currency controller for the `v1` module
 */
class CurrencyController extends ActiveController
{
    public $modelClass = Currency::class;

    /**
     * @param string $from
     * @param string $to
     * @return array
     * @throws InvalidArgumentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    public function actionRate(string $from, string $to): array
    {
        if ($from == $to) {
            throw new InvalidArgumentException('参数异常');
        }

        $items = CurrencyType::currentUseCodes();

        if ((!in_array($from, $items)) || !in_array($to, $items)) {
            throw new InvalidArgumentException('参数的值不支持');
        }

        $data = CurrencyConverter::getRates($from, $to);
        if (!data_get($data, 'success')) {
            Log::warning('获取汇率失败', $data);
            throw new InvalidArgumentException('获取汇率失败，请稍后再试');
        }

        return ['rate' => data_get($data, "rates.{$to}"), 'date' => data_get($data, 'date')];
    }
}
