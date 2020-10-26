<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\BaseStatus;
use app\core\types\BudgetPeriod;
use app\core\types\BudgetStatus;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%budget}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $amount_cent
 * @property int $period 周期, 1次 2月 3年
 * @property string|null $category_ids 分类，英文逗号隔开，默认是所有
 * @property string|null $account_ids 账户，英文逗号隔开，默认是所有
 * @property string|null $include_tags 包含标签，英文逗号隔开
 * @property string|null $exclude_tags 不包含标签，英文逗号隔开
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property int|null $status
 * @property int|null $rollover 结转
 * @property int|null $carried_balance_cent 结转余额
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Budget extends \yii\db\ActiveRecord
{
    /**
     * @var integer
     */
    public $amount;

    /**
     * @var integer
     */
    public $carried_balance;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%budget}}';
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
                'value' => Yii::$app->formatter->asDatetime('now')
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'amount', 'period', 'started_at'], 'required'],
            [['amount', 'carried_balance'], MoneyValidator::class],
            ['period', 'in', 'range' => BudgetPeriod::names()],
            ['status', 'in', 'range' => BaseStatus::names()],
            [['user_id', 'amount_cent', 'carried_balance_cent'], 'integer'],
            ['rollover', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['started_at', 'ended_at'], 'datetime', 'format' => 'php:Y-m-d'],
            [['name', 'category_ids', 'account_ids', 'include_tags', 'exclude_tags'], 'string', 'max' => 255],
        ];
    }


    /**
     * @throws InvalidArgumentException
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->status = BudgetStatus::getName($this->status);
        $this->period = BudgetPeriod::getName($this->period);
        $this->rollover = (bool)$this->rollover;
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException|InvalidConfigException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->amount_cent = Setup::toFen($this->amount);
            $this->carried_balance_cent = Setup::toFen($this->carried_balance);
            $this->period = BudgetPeriod::toEnumValue($this->period);
            $this->status = is_null($this->status) ? BudgetStatus::ACTIVE : BudgetStatus::toEnumValue($this->status);
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'period' => Yii::t('app', 'Period'),
            'category_ids' => Yii::t('app', 'Category Ids'),
            'account_ids' => Yii::t('app', 'Account Ids'),
            'include_tags' => Yii::t('app', 'Include Tags'),
            'exclude_tags' => Yii::t('app', 'Exclude Tags'),
            'started_at' => Yii::t('app', 'Started At'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'status' => Yii::t('app', 'Status'),
            'rollover' => Yii::t('app', 'Rollover'),
            'carried_balance_cent' => Yii::t('app', 'Carried Balance Cent'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id'], $fields['carried_balance_cent'], $fields['amount_cent']);

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['carried_balance'] = function (self $model) {
            return Setup::toYuan($model->carried_balance_cent);
        };

        $fields['period_text'] = function (self $model) {
            return data_get(BudgetPeriod::texts(), $model->period);
        };

        $fields['started_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->started_at);
        };

        $fields['ended_at'] = function (self $model) {
            return $model->ended_at ? DateHelper::datetimeToIso8601($model->ended_at) : null;
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
