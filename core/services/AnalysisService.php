<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Category;
use app\core\models\Record;
use app\core\models\Transaction;
use app\core\types\AnalysisDateType;
use app\core\types\DirectionType;
use app\core\types\ReimbursementStatus;
use app\core\types\TransactionType;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yiier\helpers\ArrayHelper;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;

/**
 * @property-read array $recordOverview
 */
class AnalysisService extends BaseObject
{
    /**
     * @param  array  $items
     * @param  array  $params
     * @return array
     * @throws \Exception
     */
    public function getRecordOverview(array $items, array $params = []): array
    {
        $data = [];
        $texts = AnalysisDateType::texts();
        foreach ($items as $item) {
            $date = self::getDateRange($item);
            $data[$item]['overview'] = $this->getRecordOverviewByDate($date, $params);
            $data[$item]['key'] = $item;
            $data[$item]['text'] = $texts[$item];
        }

        return $data;
    }

    public function getRecordOverviewByDate(array $date, array $params = []): array
    {
        $conditions = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }
        $baseConditions = ['user_id' => UserService::getCurrentMemberIds()];
        if ($ledgerId = data_get($params, 'ledger_id')) {
            $baseConditions += ['ledger_id' => $ledgerId];
        }

        $types = [TransactionType::EXPENSE, TransactionType::INCOME];
        $baseConditions = $baseConditions + ['transaction_type' => $types, 'exclude_from_stats' => false];
        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::INCOME])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['income'] = $sum ? (float) Setup::toYuan($sum) : 0;

        $sum = Record::find()
            ->where($baseConditions)
            ->andWhere(['direction' => DirectionType::EXPENSE])
            ->andWhere($conditions)
            ->sum('amount_cent');
        $items['expense'] = $sum ? (float) Setup::toYuan($sum) : 0;

        $items['surplus'] = (float) bcsub($items['income'], $items['expense'], 2);

        return $items;
    }

    /**
     * @param  array  $date
     * @param  int  $transactionType
     * @param  null  $ledgerId
     * @return array
     */
    public function getCategoryStatisticalData(array $date, int $transactionType, $ledgerId = null): array
    {
        $conditions = [];
        $items = [];
        if (count($date) == 2) {
            $conditions = ['between', 'date', $date[0], $date[1]];
        }

        $baseConditions = ['user_id' => UserService::getCurrentMemberIds(), 'transaction_type' => $transactionType];
        $categories = Category::find()->where($baseConditions)->asArray()->all();

        $totalCent = Record::find()
            ->where($baseConditions)
            ->andWhere(['exclude_from_stats' => false])
            ->andWhere($conditions)
            ->sum('amount_cent');
        if (!$totalCent) {
            return [];
        }
        foreach ($categories as $category) {
            $sumCent = Record::find()
                ->where($baseConditions)
                ->andWhere(['category_id' => $category['id'], 'exclude_from_stats' => false])
                ->andWhere($conditions)
                ->sum('amount_cent');
            if ($sumCent) {
                $item = [
                    'name' => $category['name'],
                    'value' => (float) Setup::toYuan($sumCent),
                    'percent' => (bcdiv($sumCent, $totalCent, 4) * 100) . '%',
                ];
                array_push($items, $item);
            }
        }

        return [
            'items' => ArrayHelper::sort2DArray($items, 'value', 'DESC'),
            'total' => (float) Setup::toYuan($totalCent),
        ];
    }

    /**
     * @param  string  $dateStr
     * @param  int  $transactionType
     * @param  null  $ledgerId
     * @return array
     * @throws InvalidConfigException
     */
    public function getRecordStatisticalData(string $dateStr, int $transactionType, $ledgerId = null): array
    {
        $dates = AnalysisDateType::getEveryDayByMonth($dateStr);

        $baseConditions = [
            'user_id' => UserService::getCurrentMemberIds(),
            'transaction_type' => $transactionType,
            'exclude_from_stats' => false,
        ];
        if ($ledgerId) {
            $baseConditions = array_merge($baseConditions, ['ledger_id' => $ledgerId]);
        }

        $items = [];
        foreach ($dates as $key => $date) {
            $items[$key]['x'] = sprintf('%02d', $key + 1);
            $sum = Record::find()
                ->where($baseConditions)
                ->andWhere(['between', 'date', $date[0], $date[1]])
                ->sum('amount_cent');
            $items[$key]['y'] = $sum ? (float) Setup::toYuan($sum) : 0;
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
            case AnalysisDateType::LAST_WEEK:
                $date = [
                    DateHelper::beginTimestamp(strtotime('last week monday')),
                    DateHelper::endTimestamp(strtotime('last week sunday')),
                ];
                break;
            case AnalysisDateType::CURRENT_MONTH:
                $time = $formatter->asDatetime('now', 'php:01-m-Y');
                $date = [DateHelper::beginTimestamp($time), DateHelper::endTimestamp()];
                break;
        }

        return $date ? array_map(function ($i) use ($formatter) {
            return $formatter->asDatetime($i);
        }, $date) : [];
    }

    /**
     * @param  array  $params
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function byCategory(array $params): array
    {
        $items = [];
        $userIds = UserService::getCurrentMemberIds();
        if ($ledgerId = data_get($params, 'ledger_id')) {
            $categoriesMap = CategoryService::getMapByLedgerId($ledgerId);
        } else {
            throw new InvalidArgumentException('ledger_id is required');
        }
        foreach ([TransactionType::EXPENSE, TransactionType::INCOME] as $type) {
            $data = $this->getBaseQuery($params)
                ->select([
                    'category_id',
                    'SUM(amount_cent) AS amount_cent',
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy('category_id')
                ->asArray()
                ->all();
            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $value) {
                $v['category_id'] = $value['category_id'];
                $v['category_name'] = data_get($categoriesMap, $value['category_id'], 0);
                $v['amount'] = (float) Setup::toYuan($value['amount_cent']);
                $items['total'][$k] += $v['amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float) bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }

    /**
     * @param  array  $params
     * @param  string  $format
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
                    'SUM(amount_cent) AS amount_cent',
                ])
                ->andWhere(['transaction_type' => $type])
                ->groupBy('m_date')
                ->asArray()
                ->all();

            $k = TransactionType::getName($type);
            $items['total'][$k] = 0;
            $items[$k] = [];
            foreach ($data as $value) {
                $v['date'] = $value['m_date'];
                $v['amount'] = (float) Setup::toYuan($value['amount_cent']);
                $items['total'][$k] += $v['amount'];
                $items[$k][] = $v;
            }
        }
        $items['total']['surplus'] = (float) bcsub(
            data_get($items['total'], TransactionType::getName(TransactionType::INCOME), 0),
            data_get($items['total'], TransactionType::getName(TransactionType::EXPENSE), 0),
            2
        );

        return $items;
    }


    /**
     * @param  array  $params
     * @return \yii\db\ActiveQuery
     * @throws \Exception
     */
    protected function getBaseQuery(array $params): \yii\db\ActiveQuery
    {
        $baseConditions = ['user_id' => UserService::getCurrentMemberIds()];
        if ($ledgerId = data_get($params, 'ledger_id')) {
            $baseConditions += ['ledger_id' => $ledgerId];
        }

        $condition = [
            'category_id' => data_get($params, 'category_id'),
            'type' => data_get($params, 'transaction_type'),
        ];
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
                'transaction_id' => array_map('intval', $transactionIds),
                'exclude_from_stats' => (int) false,
                'reimbursement_status' => [ReimbursementStatus::NONE, ReimbursementStatus::TODO],
            ])
            ->andFilterWhere([
                'account_id' => data_get($params, 'account_id'),
                'source' => data_get($params, 'source'),
            ]);
    }
}
