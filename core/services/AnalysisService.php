<?php

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Category;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\types\AnalysisDateType;
use app\core\types\DirectionType;
use app\core\types\TransactionType;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\ForbiddenHttpException;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

/**
 *
 * @property-read array $recordOverview
 */
class AnalysisService extends BaseObject
{
    /**
     * @param array $params
     * @return array
     * @throws \Exception
     */
    public function getRecordOverview(array $params = []): array
    {
        $items = [];
        foreach (AnalysisDateType::texts() as $key => $item) {
            $date = AnalysisService::getDateRange($key);
            $items[$key]['overview'] = $this->getRecordOverviewByDate($date, $params);
            $items[$key]['key'] = $key;
            $items[$key]['text'] = $item;
        }

        return $items;
    }

    public function getRecordOverviewByDate(array $date, array $params = []): array
    {
        $conditions = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }
        $baseConditions = ['user_id' => Yii::$app->user->id];
        if ($ledgerId = data_get($params, 'ledger_id')) {
            LedgerService::checkAccess($ledgerId);
            $baseConditions = ['user_id' => LedgerService::getLedgerMemberUserIds($ledgerId), 'ledger_id' => $ledgerId];
        }

        $types = [TransactionType::EXPENSE, TransactionType::INCOME];
        $baseConditions = $baseConditions + ['transaction_type' => $types, 'exclude_from_stats' => false];
        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::INCOME])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['income'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::EXPENSE])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['expense'] = $sum ? (float)Setup::toYuan($sum) : 0;

        $items['surplus'] = (float)bcsub($items['income'], $items['expense'], 2);

        return $items;
    }

    /**
     * @param array $date
     * @param int $transactionType
     * @param null $ledgerId
     * @return array
     * @throws ForbiddenHttpException
     */
    public function getCategoryStatisticalData(array $date, int $transactionType, $ledgerId = null)
    {
        $conditions = [];
        $items = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }

        $baseConditions = ['user_id' => Yii::$app->user->id];
        if ($ledgerId) {
            LedgerService::checkAccess($ledgerId);
            $baseConditions = ['user_id' => LedgerService::getLedgerMemberUserIds($ledgerId), 'ledger_id' => $ledgerId];
        }

        $baseConditions = $baseConditions + ['transaction_type' => $transactionType];
        $categories = Category::find()->where($baseConditions)->asArray()->all();

        foreach ($categories as $key => $category) {
            $items[$key]['x'] = $category['name'];
            $sum = Record::find()
                ->where($baseConditions)
                ->andWhere(['category_id' => $category['id'], 'exclude_from_stats' => false])
                ->andWhere($conditions)
                ->sum('amount_cent');
            $items[$key]['y'] = $sum ? (float)Setup::toYuan($sum) : 0;
        }

        return $items;
    }

    /**
     * @param string $dateStr
     * @param int $transactionType
     * @param null $ledgerId
     * @return array
     * @throws ForbiddenHttpException
     * @throws InvalidConfigException
     */
    public function getRecordStatisticalData(string $dateStr, int $transactionType, $ledgerId = null)
    {
        $dates = AnalysisDateType::getEveryDayByMonth($dateStr);

        $baseConditions = ['user_id' => Yii::$app->user->id];
        if ($ledgerId) {
            LedgerService::checkAccess($ledgerId);
            $baseConditions = ['user_id' => LedgerService::getLedgerMemberUserIds($ledgerId), 'ledger_id' => $ledgerId];
        }

        $baseConditions = $baseConditions + ['transaction_type' => $transactionType, 'exclude_from_stats' => false];

        $items = [];
        foreach ($dates as $key => $date) {
            $items[$key]['x'] = sprintf("%02d", $key + 1);
            $sum = Record::find()
                ->where($baseConditions)
                ->andWhere(['between', 'date', $date[0], $date[1]])
                ->sum('amount_cent');
            $items[$key]['y'] = $sum ? (float)Setup::toYuan($sum) : 0;
        }
        return $items;
    }


    /**
     * @param $key
     * @return array
     * @throws \Exception
     */
    public static function getDateRange($key): array
    {
        $formatter = Yii::$app->formatter;
        $date = [];
        switch ($key) {
            case AnalysisDateType::TODAY:
                $date = [DateHelper::beginTimestamp(), DateHelper::endTimestamp()];
                break;
            case AnalysisDateType::YESTERDAY:
                $time = strtotime('-1 day');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp($time)];
                break;
            case AnalysisDateType::LAST_MONTH:
                $beginTime = $formatter->asDatetime(strtotime('-1 month'), 'php:01-m-Y');
                $endTime = $formatter->asDatetime('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($beginTime), DateHelper::endTimestamp($endTime) - 3600 * 24];
                break;
            case AnalysisDateType::CURRENT_MONTH:
                $time = $formatter->asDatetime('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp()];
                break;
        }

        return array_map(function ($i) use ($formatter) {
            return $formatter->asDatetime($i);
        }, $date);
    }

    /**
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function byCategory(array $params)
    {
        $items = [];
        if ($ledgerId = data_get($params, 'ledger_id')) {
            LedgerService::checkAccess($ledgerId);
            $categoriesMap = CategoryService::getMapByLedgerId($ledgerId);
        } else {
            $categoriesMap = CategoryService::getMapByUserId();
        }
        foreach ([TransactionType::EXPENSE, TransactionType::INCOME] as $type) {
            $data = $this->getBaseQuery($params)
                ->select([
                    'category_id',
                    'SUM(currency_amount_cent) AS currency_amount_cent'
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy('category_id')
                ->asArray()
                ->all();
            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $key => $value) {
                $v['category_name'] = data_get($categoriesMap, $value['category_id'], 0);
                $v['currency_amount'] = (float)Setup::toYuan($value['currency_amount_cent']);
                $items['total'][$k] += $v['currency_amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float)bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }

    /**
     * @param array $params
     * @param string $format
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function byDate(array $params, string $format)
    {
        $items = [];
        foreach ([TransactionType::EXPENSE, TransactionType::INCOME] as $type) {
            $data = $this->getBaseQuery($params)
                ->select([
                    "DATE_FORMAT(date, '{$format}') as m_date",
                    'SUM(currency_amount_cent) AS currency_amount_cent'
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy('m_date')
                ->asArray()
                ->all();

            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $key => $value) {
                $v['date'] = $value['m_date'];
                $v['currency_amount'] = (float)Setup::toYuan($value['currency_amount_cent']);
                $items['total'][$k] += $v['currency_amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float)bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }


    /**
     * @param array $params
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    protected function getBaseQuery(array $params)
    {
        $baseConditions = ['user_id' => Yii::$app->user->id];
        if ($ledgerId = data_get($params, 'ledger_id')) {
            LedgerService::checkAccess($ledgerId);
            $baseConditions = ['user_id' => LedgerService::getLedgerMemberUserIds($ledgerId), 'ledger_id' => $ledgerId];
        }

        $condition = ['category_id' => request('category_id'), 'type' => request('transaction_type')];
        $query = Transaction::find()->where($baseConditions)->andFilterWhere($condition);
        if (isset($params['keyword']) && $searchKeywords = trim($params['keyword'])) {
            $query->andWhere(
                "MATCH(`description`, `tags`, `remark`) AGAINST ('*$searchKeywords*' IN BOOLEAN MODE)"
            );
        }
        if (($date = explode('~', data_get($params, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            $query->andWhere(['between', 'date', $start, $end]);
        }
        $transactionIds = $query->column();

        return Record::find()
            ->where($baseConditions)
            ->andWhere([
                'transaction_id' => $transactionIds,
                'exclude_from_stats' => (int)false,
            ])
            ->andFilterWhere([
                'account_id' => request('account_id'),
                'source' => request('source'),
            ]);
    }
}
