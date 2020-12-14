<?php

namespace app\commands;

use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\models\AuthClient;
use app\core\models\Recurrence;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\services\RecurrenceService;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\AuthClientType;
use app\core\types\RecurrenceStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class CrontabController extends Controller
{
    use ServiceTrait;

    /**
     * @throws InvalidConfigException
     * @throws Exception
     * @throws NotFoundHttpException|ThirdPartyServiceErrorException
     * @throws \Throwable
     */
    public function actionRecurrence()
    {
        /** @var Transaction[] $transactions */
        $transactions = [];
        $items = Recurrence::find()
            ->where(['status' => RecurrenceStatus::ACTIVE])
            ->andWhere(['execution_date' => Yii::$app->formatter->asDatetime('now', 'php:Y-m-d')])
            ->asArray()
            ->all();
        $transaction = Yii::$app->db->beginTransaction();
        $ids = [];
        try {
            foreach ($items as $item) {
                \Yii::$app->user->setIdentity(User::findOne($item['user_id']));
                array_push($ids, $item['id']);
                if ($newTransaction = $this->transactionService->copy($item['transaction_id'], $item['user_id'])) {
                    array_push($transactions, $newTransaction);
                    $this->stdout("定时记账成功，transaction_id：{$newTransaction->id}\n");
                }
            }
            RecurrenceService::updateAllExecutionDate($ids);
            $transaction->commit();
        } catch (\Exception $e) {
            $ids = implode(',', $ids);
            $this->stdout("定时记账失败：依次执行的 Recurrence ID 为 {$ids}，{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }

        if (count($ids) === count($items)) {
            foreach ($transactions as $transaction) {
                \Yii::$app->user->switchIdentity(User::findOne($transaction->user_id));
                $keyboard = $this->telegramService->getRecordMarkup($transaction);
                $text = $this->telegramService->getMessageTextByTransaction($transaction, '定时记账成功');
                $this->telegramService->sendMessage($text, $keyboard);
            }
        }
    }

    /**
     * @param string $type
     * @throws Exception
     */
    public function actionReport(string $type = AnalysisDateType::YESTERDAY)
    {
        $items = AuthClient::find()
            ->where(['type' => AuthClientType::TELEGRAM])
            ->asArray()
            ->all();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($items as $item) {
                $this->telegramService->sendReport($item['user_id'], $type);
                $this->stdout("定时发送报告成功，user_id：{$item['user_id']}\n");
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->stdout("定时发送报告失败：{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }
    }
}
