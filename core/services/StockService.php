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

use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\models\StockHistorical;
use app\core\traits\SendRequestTrait;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use yii\base\BaseObject;
use yii\db\Exception;
use yii\helpers\Json;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class StockService extends BaseObject
{
    use SendRequestTrait;

    /**
     * 深证成指
     * https://sg.finance.yahoo.com/quote/399001.SZ/history?p=399001.SZ&.tsrc=fin-srch
     * 上证指数
     * https://sg.finance.yahoo.com/quote/000001.SS/history?p=000001.SS&.tsrc=fin-srch
     * 恒生指数
     * https://sg.finance.yahoo.com/quote/%5EHSI/history?p=^HSI&.tsrc=fin-srch
     * 道琼斯指数
     * https://sg.finance.yahoo.com/quote/%5EDJI/history?p=^DJI&.tsrc=fin-srch
     * 纳斯达克综合指数
     * https://sg.finance.yahoo.com/quote/%5EIXIC/history?p=^IXIC&.tsrc=fin-srch
     * 标普 500 指数
     * https://sg.finance.yahoo.com/quote/%5EGSPC/history?p=%5EGSPC.
     * @param  string  $code
     * @throws ThirdPartyServiceErrorException
     */
    public static function getHistoricalData(string $code): void
    {
        $baseUrl = 'https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v3/get-historical-data';
        $params = [
            'symbol' => $code,
            'region' => 'HK',
        ];
        try {
            $self = \Yii::createObject(self::class);
            $headers = [
                'x-rapidapi-host' => 'apidojo-yahoo-finance-v1.p.rapidapi.com',
                'x-rapidapi-key' => params('rapidapiKey'),
            ];
            $rows = [];
            $response = $self->sendRequest('GET', $baseUrl, ['query' => $params, 'headers' => $headers]);
            $data = data_get(Json::decode($response), 'prices');
            $time = Carbon::now()->toDateTimeString();

            $lastDate = StockHistorical::find()->where(['code' => $code])->max('date');
            foreach ($data as $datum) {
                $row = [
                    'date' => date('Y-m-d', $datum['date']),
                    'open_price_cent' => Setup::toFen($datum['open']),
                    'current_price_cent' => Setup::toFen($datum['close']),
                    'code' => $code,
                    'created_at' => $time,
                    'updated_at' => $time,
                ];
                if ($lastDate && ($lastDate >= $row['date'])) {
                    continue;
                }
                $row['change_price_cent'] = $row['current_price_cent'] - $row['open_price_cent'];
                array_push($rows, $row);
            }
            if ($rows && !ModelHelper::saveAll(StockHistorical::tableName(), $rows)) {
                throw new Exception("stock_historical $code 基金历史数据更新失败");
            }
        } catch (GuzzleException | \Exception $e) {
            \Yii::error('stock_historical', [$response ?? [], (string) $e]);
            throw new ThirdPartyServiceErrorException();
        }
    }


    public static function getItems(): array
    {
        return [
            '399001.SZ' => '深证成指',
            '000001.SS' => '上证指数',
            '^HSI' => '恒生指数',
            '^DJI' => '道琼斯指数',
            '^IXIC' => '纳斯达克综合指数',
            '^GSPC' => '标普 500 指数',
        ];
    }
}
