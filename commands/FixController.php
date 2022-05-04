<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\commands;

use app\core\services\FixDataService;
use yii\console\Controller;

class FixController extends Controller
{
    public function actionData()
    {
        FixDataService::fixLedgerCategory();
        $this->stdout("操作成功\n");
    }

    public function actionUserParent()
    {
        FixDataService::fixChildUserData();
        $this->stdout("操作成功\n");
    }
}
