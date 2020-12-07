<?php

namespace app\core\jobs;

use app\core\services\BudgetService;
use yii\base\BaseObject;

class UpdateBudgetJob extends BaseObject implements \yii\queue\JobInterface
{

    public $ledgerId;
    public $datetime;

    /**
     * @param \yii\queue\Queue $queue
     * @return mixed|void
     * @throws \yii\db\Exception
     */
    public function execute($queue)
    {
        BudgetService::updateBudgetActualAmount($this->ledgerId, $this->datetime);
    }
}
