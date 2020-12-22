<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\models\Record;
use app\core\services\LedgerService;
use app\core\traits\ServiceTrait;
use app\core\types\RecordSource;
use app\core\types\TransactionType;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;

/**
 * Record controller for the `v1` module
 */
class RecordController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Record::class;
    public $noAuthActions = [];
    public $defaultOrder = ['date' => SORT_DESC, 'id' => SORT_DESC];
    public $stringToIntAttributes = ['transaction_type' => TransactionType::class];

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
     * @throws \app\core\exceptions\InternalException
     * @throws \yii\base\InvalidConfigException
     */
    public function prepareDataProvider()
    {
        $dataProvider = parent::prepareDataProvider();

        $params = Yii::$app->request->queryParams;
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
     * @param array $params
     * @return array
     * @throws Exception
     */
    protected function formatParams(array $params)
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
    public function actionOverview()
    {
        $params = Yii::$app->request->queryParams;
        return array_values($this->analysisService->getRecordOverview($params));
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function actionAnalysis()
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
    public function actionSources()
    {
        $items = [];
        $names = RecordSource::names();
        foreach ($names as $key => $name) {
            $items[] = ['type' => $key, 'name' => $name];
        }
        return $items;
    }

    /**
     * @param string $action
     * @param null $model
     * @param array $params
     * @throws ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['delete', 'update'])) {
            LedgerService::checkAccessOnType($model->ledger_id, $model->user_id, $action);
            LedgerService::checkAccess($model->ledger_id, RuleControlHelper::EDIT);
        }
    }
}
