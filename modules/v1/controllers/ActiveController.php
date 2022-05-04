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

use app\core\actions\CreateAction;
use app\core\actions\UpdateAction;
use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\helpers\SearchHelper;
use app\core\models\User;
use app\core\services\UserProService;
use app\core\services\UserService;
use app\core\types\UserRole;
use bizley\jwt\JwtHttpBearerAuth;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\filters\Cors;
use yii\web\ForbiddenHttpException;
use yiier\helpers\SearchModel;
use yiier\helpers\Setup;

/**
 * @property-read int $pageSize
 */
class ActiveController extends \yii\rest\ActiveController
{
    protected const MAX_PAGE_SIZE = 100;
    protected const DEFAULT_PAGE_SIZE = 20;
    public array $defaultOrder = ['id' => SORT_DESC];
    public array $partialMatchAttributes = [];
    public array $stringToIntAttributes = [];
    public array $relations = [];

    /**
     * 不参与校验的 actions.
     * @var array
     */
    public array $noAuthActions = [];

    // 序列化输出
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        // 跨区请求 必须先删掉 authenticator
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Max-Age' => 86400,
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'optional' => array_merge($this->noAuthActions, ['options']),
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['create']['class'] = CreateAction::class;
        $actions['update']['class'] = UpdateAction::class;
        return $actions;
    }

    /**
     * @return ActiveDataProvider
     * @throws InvalidArgumentException
     * @throws InternalException
     * @throws InvalidConfigException
     * @throws \Exception
     */
    public function prepareDataProvider()
    {
        /** @var ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $searchModel = new SearchModel([
            'defaultOrder' => $this->defaultOrder,
            'model' => $modelClass,
            'relations' => $this->relations,
            'scenario' => 'default',
            'partialMatchAttributes' => $this->partialMatchAttributes,
            'pageSize' => $this->getPageSize(),
        ]);

        $params = $this->formatParams(Yii::$app->request->queryParams);
        foreach ($this->stringToIntAttributes as $attribute => $className) {
            if ($type = data_get($params, $attribute)) {
                $params[$attribute] = SearchHelper::stringToInt($type, $className);
            }
        }
        unset($params['sort']);

        $dataProvider = $searchModel->search(['SearchModel' => $params]);

        $dataProvider->query->andWhere([$modelClass::tableName() . '.user_id' => UserService::getCurrentMemberIds()]);
        return $dataProvider;
    }


    protected function formatParams(array $params): array
    {
        return $params;
    }

    protected function getPageSize(): int
    {
        if ($pageSize = (int) request('pageSize')) {
            if ($pageSize <= self::MAX_PAGE_SIZE) {
                return $pageSize;
            }
            return self::MAX_PAGE_SIZE;
        }
        return self::DEFAULT_PAGE_SIZE;
    }


    /**
     * @param  Model  $model
     * @param  array  $params
     * @return Model
     * @throws InvalidArgumentException
     */
    public function validate(Model $model, array $params): Model
    {
        $model->load($params, '');
        if (!$model->validate()) {
            throw new InvalidArgumentException(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param  string  $action
     * @param  null  $model
     * @param  array  $params
     * @throws ForbiddenHttpException|UserNotProException|InvalidArgumentException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        UserProService::checkAccess($this->modelClass, $action, $model);
        if (in_array($action, ['delete', 'update', 'view', 'update-status'])) {
            if (!in_array($model->user_id, UserService::getCurrentMemberIds())) {
                throw new ForbiddenHttpException(
                    t('app', 'You can only ' . $action . ' data that you\'ve created.')
                );
            }
        }

        if (in_array($action, ['delete', 'update', 'update-status', 'create'])) {
            $userRole = User::find()->select('role')->where(['id' => Yii::$app->user->id])->scalar();
            if (!in_array($userRole, [UserRole::ROLE_READ_WRITE, UserRole::ROLE_OWNER])) {
                throw new ForbiddenHttpException(
                    t('app', 'You not have permission to ' . $action . ' data.')
                );
            }
        }
    }
}
