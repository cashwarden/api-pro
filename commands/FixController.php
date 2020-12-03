<?php

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
}
