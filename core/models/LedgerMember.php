<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\helpers\RuleControlHelper;
use app\core\types\LedgerMemberRule;
use app\core\types\LedgerMemberStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%ledger_member}}".
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $user_id
 * @property int|string $rule
 * @property int|string $permission
 * @property int|string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read User $user
 */
class LedgerMember extends \yii\db\ActiveRecord
{
    public $rule;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ledger_member}}';
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => Yii::$app->formatter->asDatetime('now'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ledger_id', 'user_id', 'rule', 'status'], 'required'],
            [['ledger_id', 'user_id', 'permission'], 'integer'],
            ['rule', 'in', 'range' => LedgerMemberRule::names()],
            ['status', 'in', 'range' => LedgerMemberStatus::names()],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ledger_id' => Yii::t('app', 'Ledger ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'rule' => Yii::t('app', 'Rule'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->permission = RuleControlHelper::getPermissionByRule($this->rule);
            $this->status = LedgerMemberStatus::toEnumValue($this->status);
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id'], $fields['permission']);

        $rule = RuleControlHelper::getRuleByPermission($this->permission);

        $fields['status'] = function (self $model) {
            return LedgerMemberStatus::getName($model->status);
        };

        $fields['status_txt'] = function (self $model) {
            return LedgerMemberStatus::texts()[$model->status];
        };

        $fields['rule'] = function (self $model) use ($rule) {
            return LedgerMemberRule::names()[$rule];
        };

        $fields['rule_txt'] = function (self $model) use ($rule) {
            return LedgerMemberRule::texts()[$rule];
        };

        $fields['user'] = function (self $model) {
            return $model->user;
        };

        $fields['created_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->created_at);
        };

        $fields['updated_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->updated_at);
        };

        return $fields;
    }
}
