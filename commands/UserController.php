<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\commands;

use app\core\models\User;
use app\core\services\UserProService;
use Carbon\Carbon;
use yii\console\Controller;
use yii\db\Exception;
use yii\helpers\Console;

class UserController extends Controller
{
    public function actionUpgradePro(int $userId, string $date = '')
    {
        $carbon = $date ? Carbon::parse($date) : Carbon::now()->addYear();
        $endedAt = $carbon->endOfDay();
        try {
            if (!User::findOne($userId)) {
                throw new Exception('用户不存在');
            }
            if ($record = UserProService::upgradeToProBySystem($userId, $endedAt)) {
                $this->stdout(json_encode($record->attributes) . "\n", Console::FG_GREEN);
                $this->stdout("成功\n", Console::FG_GREEN);
            }
        } catch (\Exception $e) {
            $this->stdout("失败 {$e->getMessage()}\n", Console::FG_RED);
        }
    }
}
