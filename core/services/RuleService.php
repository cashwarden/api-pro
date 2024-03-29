<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\helpers\ArrayHelper;
use app\core\models\Rule;
use app\core\types\RuleStatus;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yiier\helpers\Setup;

class RuleService
{
    /**
     * @param  int  $id
     * @return Rule
     * @throws NotFoundHttpException
     * @throws Exception
     */
    public function copy(int $id): Rule
    {
        $model = $this->findCurrentOne($id);
        $rule = new Rule();
        $values = $model->toArray();
        $rule->load($values, '');
        $rule->name = $rule->name . ' Copy';
        if (!$rule->save(false)) {
            throw new Exception(Setup::errorMessage($rule->firstErrors));
        }
        return Rule::findOne($rule->id);
    }


    /**
     * @throws Exception
     */
    public function updateStatus(Rule $rule, string $status): Rule
    {
        $rule->load($rule->toArray(), '');
        $rule->status = $status;
        if (!$rule->save(false)) {
            throw new Exception(Setup::errorMessage($rule->firstErrors));
        }
        return $rule;
    }


    /**
     * @param  string  $desc
     * @return Rule[]
     */
    public function getRulesByDesc(string $desc): array
    {
        $models = Rule::find()
            ->where(['user_id' => UserService::getCurrentMemberIds(), 'status' => RuleStatus::ACTIVE])
            ->orderBy(['sort' => SORT_ASC, 'id' => SORT_DESC])
            ->all();
        $rules = [];
        /** @var Rule $model */
        foreach ($models as $model) {
            $ifKeywords = ArrayHelper::strPosArr($desc, explode(',', $model->if_keywords)) !== false;
            if ($model->if_keywords === '*' || $ifKeywords) {
                array_push($rules, $model);
            }
        }
        return $rules;
    }

    /**
     * @param  int  $id
     * @return Rule
     * @throws NotFoundHttpException
     */
    public function findCurrentOne(int $id): Rule
    {
        $userIds = UserService::getCurrentMemberIds();
        if (!$model = Rule::find()->where(['id' => $id, 'user_id' => $userIds])->one()) {
            throw new NotFoundHttpException('No data found');
        }
        return $model;
    }
}
