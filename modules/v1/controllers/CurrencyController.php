<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\CurrencyConverter;
use app\core\models\Currency;
use app\core\models\Ledger;
use app\core\services\LedgerService;
use app\core\types\CurrencyType;
use Yii;
use yiier\graylog\Log;

/**
 * Currency controller for the `v1` module.
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


    public function actionCodes(): array
    {
        $items = [];
        $names = CurrencyType::names();
        foreach (CurrencyType::currentUseCodes() as $currentUseCode) {
            array_push($items, ['code' => $currentUseCode, 'name' => $names[$currentUseCode]]);
        }
        return $items;
    }

    public function actionCanUseCodes(): array
    {
        $items = [];
        $names = CurrencyType::names();
        $userIds = Yii::$app->user->id;
        if ($ledgerId = data_get(Yii::$app->request->queryParams, 'ledger_id')) {
            LedgerService::checkAccess($ledgerId);
            $userIds = LedgerService::getLedgerMemberUserIds($ledgerId);
        }
        $baseCode = Ledger::find()->select('base_currency_code')
            ->where(['id' => $ledgerId, 'user_id' => $userIds])
            ->scalar();
        array_push($items, ['code' => $baseCode, 'name' => $names[$baseCode]]);

        $codes = Currency::find()->select('currency_code_from')
            ->where(['ledger_id' => $ledgerId, 'user_id' => $userIds])
            ->asArray()
            ->column();
        foreach ($codes as $code) {
            array_push($items, ['code' => $code, 'name' => $names[$code]]);
        }

        return $items;
    }
}
