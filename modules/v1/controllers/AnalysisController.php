<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\models\Ledger;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\traits\ServiceTrait;
use app\core\types\AnalysisGroupDateType;

class AnalysisController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = '';
    public array $noAuthActions = [];

    public function actions(): array
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \app\core\exceptions\UserNotProException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCategory(): array
    {
        $params = \Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id, null, $params);
        return $this->analysisService->byCategory($params);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws \app\core\exceptions\UserNotProException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDate(): array
    {
        $params = \Yii::$app->request->queryParams;
        $groupByDateType = request('group_type') ?: AnalysisGroupDateType::DAY;
        $this->checkAccess($this->action->id, null, $params);
        return $this->analysisService->byDate($params, AnalysisGroupDateType::getValue($groupByDateType));
    }


    /**
     * @throws UserNotProException
     * @throws InvalidArgumentException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionCalendar(): array
    {
        if (!UserProService::isPro()) {
            throw new UserNotProException();
        }
        $params = \Yii::$app->request->queryParams;
        $this->checkAccess($this->action->id, null, $params);
        $groupByDateType = request('group_type') ?: AnalysisGroupDateType::DAY;
        return $this->analysisService->byCalendar($params, AnalysisGroupDateType::getValue($groupByDateType));
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (!isset($params['ledger_id'])) {
            throw new InvalidArgumentException('ledger_id is required');
        }
        $model = Ledger::findOne($params['ledger_id']);
        if ($model && !in_array($model->user_id, UserService::getCurrentMemberIds())) {
            throw new InvalidArgumentException('You are not allowed to perform this action.');
        }
    }
}
