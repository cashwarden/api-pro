<?php

/**
 * author     : forecho <caizhenghai@gmail.comm>
 * createTime : 2019/6/2 8:29 PM
 * description:
 */

namespace app\core\helpers;

use GuzzleHttp\Client;

class CurrencyConverter
{
    public const ENDPOINT = 'https://api.exchangerate.host/latest';

    /**
     * 获取汇率
     * @param string $base
     * @param string $symbols
     * @return array
     * [
     *    'base' => 'CNY'
     *    'rates' => [
     *      'AUD' => 0.2094360439
     *      'JPY' => 15.7401518593
     *      'GBP' => 0.1151184373
     *      'MXN' => 2.8414822506
     *      'CAD' => 0.1961840483
     *      'USD' => 0.1447335972
     *      'EUR' => 0.1297942761
     *    ]
     *   'date' => '2019-05-31'
     * ]
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getRates(string $base, string $symbols = 'CNY'): array
    {
        $client = new Client();
        $response = $client->get(self::ENDPOINT, [
            'query' => [
                'base' => $base,
                'symbols' => $symbols,
            ]
        ]);
        return json_decode($response->getBody(), true);
    }
}
