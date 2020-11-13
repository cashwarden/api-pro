<?php

namespace app\commands;

use app\core\services\FixDataService;
use yii\console\Controller;

class FixController extends Controller
{
    public function actionInitLedger()
    {
        FixDataService::initLedger();
        $this->stdout("操作成功\n");
    }
}
