<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

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
