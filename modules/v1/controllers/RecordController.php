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

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\Record;
use app\core\requests\UpdateStatus;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\ExcludeFromStats;
use app\core\types\RecordSource;
use app\core\types\ReimbursementStatus;
use app\core\types\ReviewStatus;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yiier\helpers\Setup;

/**
 * Record controller for the `v1` module.
 */
class RecordController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Record::class;
    public array $noAuthActions = [];
    public array $defaultOrder = ['date' => SORT_DESC, 'id' => SORT_DESC];
    public array $stringToIntAttributes = [
        'transaction_type' => TransactionType::class,
        'reimbursement_status' => ReimbursementStatus::class,
    ];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update']);
        return $actions;
    }

    /**
     * @return ActiveDataProvider
     * @throws ForbiddenHttpException
     * @throws InvalidArgumentException
     * @throws InternalException
     * @throws \yii\base\InvalidConfigException
     */
    public function prepareDataProvider()
    {
        $dataProvider = parent::prepareDataProvider();

        $params = $this->formatParams(Yii::$app->request->queryParams);
        $transactionIds = params('useXunSearch')
            ? $this->transactionService->getIdsByXunSearch($params)
            : $this->transactionService->getIdsBySearch($params);
        if (!empty($params['account_id']) && empty($params['ledger_id'])) {
            array_push($transactionIds, 0);
        }
        $dataProvider->query->andWhere(['transaction_id' => $transactionIds]);

        $dataProvider->setModels($this->transactionService->formatRecords($dataProvider->getModels()));
        return $dataProvider;
    }

    /**
     * @param  array  $params
     * @return array
     * @throws Exception
     */
    protected function formatParams(array $params): array
    {
        if (($date = explode('~', data_get($params, 'date'))) && count($date) == 2) {
            $start = $date[0] . ' 00:00:00';
            $end = $date[1] . ' 23:59:59';
            $params['date'] = "{$start}~{$end}";
        }
        return $params;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionOverview(): array
    {
        $params = Yii::$app->request->queryParams;
        $items = [
            AnalysisDateType::TODAY,
            AnalysisDateType::YESTERDAY,
            AnalysisDateType::CURRENT_MONTH,
            AnalysisDateType::LAST_MONTH,
            AnalysisDateType::GRAND_TOTAL,
        ];
        return array_values($this->analysisService->getRecordOverview($items, $params));
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function actionAnalysis(): array
    {
        $transactionType = request('transaction_type', TransactionType::getName(TransactionType::EXPENSE));
        $date = request('date', Yii::$app->formatter->asDatetime('now'));

        return $this->analysisService->getRecordStatisticalData(
            $date,
            TransactionType::toEnumValue($transactionType),
            request('ledger_id')
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionSources(): array
    {
        $items = [];
        $names = RecordSource::names();
        foreach ($names as $key => $name) {
            $items[] = ['type' => $key, 'name' => $name];
        }
        return $items;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionReimbursementStatuses(): array
    {
        $items = [];
        $texts = ReimbursementStatus::text();
        foreach ($texts as $key => $text) {
            $items[] = ['type' => ReimbursementStatus::getName($key), 'name' => $text];
        }
        return $items;
    }

    /**
     * @param  string  $key
     * @param  int  $id
     * @return bool
     * @throws ForbiddenHttpException
     * @throws InternalException
     * @throws InvalidArgumentException
     * @throws \yii\db\Exception
     * @throws \app\core\exceptions\UserNotProException
     */
    public function actionUpdateStatus(string $key, int $id): bool
    {
        $params = Yii::$app->request->bodyParams;
        $record = Record::findOne($id);
        $this->checkAccess($this->action->id, $record);
        switch ($key) {
            case 'reimbursement_status':
                $statusClass = new ReimbursementStatus();
                if ($record->transaction_type != TransactionType::EXPENSE) {
                    throw new InvalidArgumentException();
                }
                break;
            case 'exclude_from_stats':
                $statusClass = new ExcludeFromStats();
                break;
            case 'review':
                $statusClass = new ReviewStatus();
                break;
            default:
                throw new InvalidArgumentException('错误的 key');
        }

        $model = new UpdateStatus($statusClass::names());
        /** @var UpdateStatus $requestModel */
        $requestModel = $this->validate($model, $params);
        $record->$key = $statusClass::toEnumValue($requestModel->status);

        if (!$record->save(false)) {
            throw new \yii\db\Exception(Setup::errorMessage($record->firstErrors));
        }
        return true;
    }
}
