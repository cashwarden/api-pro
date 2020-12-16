<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\models\Transaction;
use app\core\requests\TransactionCreateByDescRequest;
use app\core\requests\TransactionUploadRequest;
use app\core\services\LedgerService;
use app\core\traits\ServiceTrait;
use app\core\types\TransactionType;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;

/**
 * Transaction controller for the `v1` module
 */
class TransactionController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Transaction::class;
    public $noAuthActions = [];

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['delete']);
        return $actions;
    }

    /**
     * @return Transaction
     * @throws \Exception|\Throwable
     */
    public function actionCreateByDescription(): Transaction
    {
        $params = Yii::$app->request->bodyParams;
        $model = new TransactionCreateByDescRequest();
        /** @var TransactionCreateByDescRequest $model */
        $model = $this->validate($model, $params);

        return $this->transactionService->createByDesc($model->description);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes(): array
    {
        $items = [];
        $texts = TransactionType::texts();
        $names = TransactionType::names();
        // unset($names[TransactionType::ADJUST]);
        foreach ($names as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }


    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public function actionUpload(): array
    {
        $fileParam = 'file';
        $uploadedFile = UploadedFile::getInstanceByName($fileParam);
        $params = [$fileParam => $uploadedFile];
        $model = new TransactionUploadRequest();
        $this->validate($model, $params);
        $filename = Yii::$app->user->id . 'record.csv';
        $this->uploadService->uploadRecord($uploadedFile, $filename);
        $data = $this->transactionService->createByCSV($filename);
        $this->uploadService->deleteLocalFile($filename);
        return $data;
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function actionExport(): array
    {
        return $this->transactionService->exportData();
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
