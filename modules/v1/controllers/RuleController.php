<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
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
     * @param int $id
     * @return Rule
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function actionCopy(int $id): Rule
    {
        return $this->ruleService->copy($id);
    }

    /**
     * @param int $id
     * @return Rule
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \app\core\exceptions\InternalException|NotFoundHttpException
     */
    public function actionUpdateStatus(int $id): Rule
    {
        $params = Yii::$app->request->bodyParams;
        $model = new UpdateStatus(RuleStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->ruleService->updateStatus($id, $model->status);
    }
}
