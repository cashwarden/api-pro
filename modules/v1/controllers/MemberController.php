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
use app\core\models\User;
use app\core\requests\MemberFormRequest;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\traits\ServiceTrait;
use app\core\types\UserRole;
use Yii;
use yii\base\Exception;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Exception as DBException;
use yii\web\ForbiddenHttpException;
use yiier\helpers\SearchModel;

class MemberController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = User::class;

    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['update'], $actions['delete'], $actions['create']);
        // 注销系统自带的实现方法
        return $actions;
    }

    public function prepareDataProvider(): ActiveDataProvider
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => $this->defaultOrder,
            'model' => $modelClass,
            'scenario' => 'default',
            'pageSize' => $this->getPageSize(),
        ]);

        $params = $this->formatParams(Yii::$app->request->queryParams);
        $dataProvider = $searchModel->search(['SearchModel' => $params]);
        $dataProvider->query->andWhere(['id' => UserService::getCurrentMemberIds()]);
        return $dataProvider;
    }


    /**
     * @throws Exception
     * @throws DBException
     * @throws InvalidArgumentException
     */
    public function actionCreate(): User
    {
        /** @var User $parent */
        $parent = Yii::$app->user->identity;
        $this->checkAccess($this->action->id, $parent);
        $params = Yii::$app->request->bodyParams;
        /** @var MemberFormRequest $data */
        $data = $this->validate(new MemberFormRequest(), $params);
        $user = new User();
        $user = $this->userService->createUpdateMember($data, $user, $parent);
        if (params('verificationEmail')) {
            // 重试3次
            for ($i = 0; $i < 3; $i++) {
                if ($this->mailerService->sendConfirmationMessage($user)) {
                    break;
                }
            }
        }
        return $user;
    }

    /**
     * @throws DBException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function actionUpdate(int $id): User
    {
        /** @var User $parent */
        $parent = Yii::$app->user->identity;
        if (!$user = User::find()->where(['id' => $id, 'parent_id' => $parent->id])->one()) {
            throw new ForbiddenHttpException('您无权操作该用户');
        }
        $this->checkAccess($this->action->id, $parent);
        $params = Yii::$app->request->bodyParams;
        $model = new MemberFormRequest();
        $model->id = $id;
        /** @var MemberFormRequest $data */
        $data = $this->validate($model, $params);
        return $this->userService->createUpdateMember($data, $user, $parent);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function actionTypes(): array
    {
        $items = [];
        $texts = UserRole::texts();
        $names = UserRole::names();
        unset($names[UserRole::ROLE_OWNER]);
        foreach ($names as $key => $name) {
            $items[] = ['type' => $name, 'name' => data_get($texts, $key)];
        }
        return $items;
    }


    /**
     * @param  string  $action
     * @param  null  $model
     * @param  array  $params
     * @throws ForbiddenHttpException|UserNotProException|InvalidArgumentException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (in_array($action, ['create', 'update', 'delete'])) {
            if ($model->parent_id) {
                throw new ForbiddenHttpException(
                    t('app', 'You can not create a user under the user')
                );
            }
        }
        UserProService::checkAccess($this->modelClass, $action, $model);
    }
}
