<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\Ledger;
use app\core\requests\LedgerInvitingMember;
use app\core\traits\ServiceTrait;
use app\core\types\LedgerType;
use Yii;

/**
 * Ledger controller for the `v1` module
 */
class LedgerController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Ledger::class;

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        return $actions;
    }

    /**
     * @return bool
     * @throws InternalException
     * @throws InvalidArgumentException
     */
    public function actionInvitingMember()
    {
        $model = new LedgerInvitingMember();
        $params = Yii::$app->request->bodyParams;
        $this->validate($model, $params);
        $model->validateGroup();

        return $this->ledgerService->invitingMember($model);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes()
    {
        $items = [];
        $texts = LedgerType::texts();
        foreach (LedgerType::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }
}
