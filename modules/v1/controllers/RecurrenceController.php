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

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\models\Recurrence;
use app\core\requests\UpdateStatus;
use app\core\traits\ServiceTrait;
use app\core\types\RecurrenceFrequency;
use app\core\types\RecurrenceStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

/**
 * Recurrence controller for the `v1` module.
 */
class RecurrenceController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = Recurrence::class;
    public array $partialMatchAttributes = ['name'];

    /**
     * @param int $id
     * @return Recurrence
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     * @throws InternalException
     */
    public function actionUpdateStatus(int $id): Recurrence
    {
        $params = Yii::$app->request->bodyParams;
        $model = new UpdateStatus(RecurrenceStatus::names());
        /** @var UpdateStatus $model */
        $model = $this->validate($model, $params);

        return $this->recurrenceService->updateStatus($id, $model->status);
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function actionFrequencyTypes(): array
    {
        $items = [];
        $texts = RecurrenceFrequency::texts();
        foreach (RecurrenceFrequency::names() as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }
}
