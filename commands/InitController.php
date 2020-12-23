<?php

namespace app\commands;

use app\core\models\Search;
use app\core\models\Transaction;
use app\core\models\User;
use app\core\services\TelegramService;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\traits\FixDataTrait;
use app\core\traits\ServiceTrait;
use Carbon\Carbon;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Url;

class InitController extends Controller
{
    use ServiceTrait;
    use FixDataTrait;

    /**
     * @var bool|mixed
     */
    private $count;

    public function actionTelegram()
    {
        $url = Url::to('/v1/telegram/hook', true);
        TelegramService::newClient()->setWebHook($url);
        $this->stdout("Telegram set Webhook url success!: {$url}\n");
        return ExitCode::OK;
    }

    /**
     * @param int $userId
     * @throws \app\core\exceptions\InvalidArgumentException
     * @throws \yii\db\Exception
     */
    public function actionUserData(int $userId)
    {
        $this->userService->createUserAfterInitData(User::findOne($userId));
        $this->stdout("User Account and Category init success! \n");
    }

    public function actionXunSearch()
    {
        $query = Transaction::find();
        $search = new Search();
        $search::getDb()->getIndex()->clean();
        $this->migrate(
            $query,
            function ($item) {
                return false;
            },
            function (Transaction $item) {
                $this->count += Search::createUpdate(true, $item);
            },
            false
        );
        $search::getDb()->getIndex()->flushIndex();
        $this->stdout("刷新了 {$this->count} 条数据\n");
    }

    public function actionUserPro()
    {
        $query = User::find();
        $this->migrate(
            $query,
            function ($item) {
                return false;
            },
            function (User $item) {
                $endedAt = Carbon::parse("2020-12-31")->endOfDay();
                if (UserProService::upgradeToProBySystem($item->id, $endedAt)) {
                    $this->count++;
                }
            },
            false
        );
        $this->stdout("添加了 {$this->count} 条数据\n");
    }
}
