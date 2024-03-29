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

use app\core\models\Rule;
use app\core\requests\UpdateStatus;
use app\core\traits\ServiceTrait;
use app\core\types\RuleStatus;
use Yii;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Rule controller for the `v1` module.
 */
class RuleController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Rule::class;
    public array $defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_DESC];
    public array $partialMatchAttributes = ['name'];

    /**
     * @param  int  $id
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy(int $id): Rule
    {
        return $this->ruleService->copy($id);
    }


    /**
     * @param  int  $id
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \app\core\exceptions\InternalException
     * @throws \app\core\exceptions\InvalidArgumentException
     * @throws \app\core\exceptions\UserNotProException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUpdateStatus(int $id): Rule
    {
        $params = Yii::$app->request->bodyParams;
        $role = $this->ruleService->findCurrentOne($id);
        $this->checkAccess($this->action->id, $role);
        $model = new UpdateStatus(RuleStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->ruleService->updateStatus($role, $model->status);
    }
}
