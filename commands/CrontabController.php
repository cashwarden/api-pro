<?php

namespace app\commands;

use app\core\exceptions\ThirdPartyServiceErrorException;
use app\core\models\AuthClient;
use app\core\models\Recurrence;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\services\RecurrenceService;
use app\core\services\StockService;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisDateType;
use app\core\types\AuthClientType;
use app\core\types\RecurrenceStatus;
use app\core\types\UserSettingKeys;
use Yii;
use yii\base\InvalidConfigException;
use yii\console\Controller;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\userSetting\models\UserSettingModel;

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
        $date = Yii::$app->formatter->asDatetime('now', 'php:Y-m-d');
        $items = Recurrence::find()
            ->where(['status' => RecurrenceStatus::ACTIVE, 'execution_date' => $date])
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
                    $this->stdout("{$date} 定时记账成功，transaction_id：{$newTransaction->id}\n");
                }
            }
            RecurrenceService::updateAllExecutionDate($ids);
            $transaction->commit();
        } catch (\Exception $e) {
            $ids = implode(',', $ids);
            $this->stdout("{$date} 定时记账失败：依次执行的 Recurrence ID 为 {$ids}，{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }

        if (count($ids) === count($items)) {
            foreach ($transactions as $transaction) {
                \Yii::$app->user->switchIdentity(User::findOne($transaction->user_id));
                $keyboard = $this->telegramService->getTransactionMarkup($transaction);
                $text = $this->telegramService->getMessageTextByTransaction($transaction, '定时记账成功');
                $this->telegramService->sendMessage($text, $keyboard);
            }
        }
    }

    /**
     * 0 10 * * * 每天的 10:00 执行 php yii crontab/report yesterday
     * 5 10 * * 1 每周一的 10:05 执行 php yii crontab/report last_week
     * 10 10 1 * * 每月1日的 10:10 执行 php yii crontab/report last_month
     * @param string $type
     * @throws Exception
     */
    public function actionReport(string $type = AnalysisDateType::YESTERDAY)
    {
        $userIds = AuthClient::find()->where(['type' => AuthClientType::TELEGRAM])->select('user_id')->column();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            switch ($type) {
                case AnalysisDateType::LAST_WEEK:
                    $key = UserSettingKeys::WEEKLY_REPORT;
                    break;
                case AnalysisDateType::LAST_MONTH:
                    $key = UserSettingKeys::MONTHLY_REPORT;
                    break;
                case AnalysisDateType::YESTERDAY:
                    $key = UserSettingKeys::DAILY_REPORT;
                    break;
                default:
                    $key = 0;
                    break;
            }
            $sendUserIds = UserSettingModel::find()
                ->select('user_id')
                ->where(['user_id' => $userIds, 'key' => $key, 'value' => '1'])
                ->column();
            foreach ($sendUserIds as $sendUserId) {
                $this->telegramService->sendReport($sendUserId, $type);
                $this->stdout("定时发送报告成功，user_id：{$sendUserId}\n");
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->stdout("定时发送报告失败：{$e->getMessage()}\n");
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionUpdateStock()
    {
        foreach (StockService::getItems() as $key => $item) {
            StockService::getHistoricalData($key);
        }
    }
}
