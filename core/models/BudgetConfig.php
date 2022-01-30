<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\services\BudgetService;
use app\core\types\BaseStatus;
use app\core\types\BudgetPeriod;
use app\core\types\BudgetStatus;
use app\core\types\TransactionType;
use Carbon\Carbon;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\ArrayValidator;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%budget_config}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property int $amount_cent
 * @property int|null $init_amount_cent
 * @property int $period 周期，1一次性 2月 3年
 * @property int $ledger_id
 * @property int $transaction_type
 * @property string|array $category_ids 分类，英文逗号隔开，默认是所有
 * @property string|null $include_tags 包含标签，英文逗号隔开
 * @property string|null $exclude_tags 不包含标签，英文逗号隔开
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property int|null $status
 * @property int|null $rollover 结转
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read array $budgetProgress
 */
class BudgetConfig extends \yii\db\ActiveRecord
{
    /**
     * @var int
     */
    public $amount;

    /**
     * @var int
     */
    public $init_amount;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%budget_config}}';
    }


    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
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
            [
                ['name', 'ledger_id', 'transaction_type', 'amount', 'period', 'category_ids', 'started_at', 'ended_at'],
                'required',
            ],
            [['amount', 'init_amount'], MoneyValidator::class],
            ['period', 'in', 'range' => BudgetPeriod::names()],
            ['status', 'in', 'range' => BaseStatus::names()],
            [
                'transaction_type',
                'in',
                'range' => [
                    TransactionType::getName(TransactionType::EXPENSE),
                    TransactionType::getName(TransactionType::INCOME),
                ],
            ],
            [
                'ledger_id',
                'exist',
                'targetClass' => Ledger::class,
                'filter' => ['user_id' => Yii::$app->user->id],
                'targetAttribute' => 'id',
            ],
            ['category_ids', ArrayValidator::class], // todo 其他验证
            [['user_id', 'ledger_id', 'amount_cent', 'init_amount_cent'], 'integer'],
            ['rollover', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['started_at', 'ended_at'], 'datetime', 'format' => 'php:Y-m-d'],
            [['name', 'include_tags', 'exclude_tags'], 'string', 'max' => 255],
            ['ended_at', 'compare', 'compareAttribute' => 'started_at', 'operator' => '>='],
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->status = BudgetStatus::getName($this->status);
        $this->rollover = (bool) $this->rollover;
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
            $this->init_amount_cent = Setup::toFen($this->init_amount);
            $this->period = BudgetPeriod::toEnumValue($this->period);
            $this->formatDate();
            $this->transaction_type = TransactionType::toEnumValue($this->transaction_type);
            $this->status = $this->status === null ? BudgetStatus::ACTIVE : BudgetStatus::toEnumValue($this->status);
            $this->category_ids = $this->category_ids ? implode(',', array_unique($this->category_ids)) : null;
            return true;
        }
        return false;
    }


    public function formatDate()
    {
        $d1 = new Carbon($this->started_at);
        $d2 = new Carbon($this->ended_at);
        switch ($this->period) {
            case BudgetPeriod::MONTH:
                $this->started_at = $d1->firstOfMonth();
                $this->ended_at = $d2->endOfMonth();
                break;
            case BudgetPeriod::YEAR:
                $this->started_at = $d1->firstOfYear();
                $this->ended_at = $d2->endOfYear();
                break;
            default:
                $this->started_at = $d1;
                $this->ended_at = $d2->endOfDay();
                break;
        }
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception|\Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!$insert) {
            Budget::deleteAll(['user_id' => $this->user_id, 'budget_config_id' => $this->id]);
        }
        BudgetService::createUpdateBudgetConfigAfter($this);
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
            'amount_cent' => Yii::t('app', 'Budget Amount Cent'),
            'init_amount_cent' => Yii::t('app', 'Init Budget Amount Cent'),
            'period' => Yii::t('app', 'Period'),
            'ledger_id' => Yii::t('app', 'Ledger ID'),
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'category_ids' => Yii::t('app', 'Category Ids'),
            'include_tags' => Yii::t('app', 'Include Tags'),
            'exclude_tags' => Yii::t('app', 'Exclude Tags'),
            'started_at' => Yii::t('app', 'Started At'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'status' => Yii::t('app', 'Status'),
            'rollover' => Yii::t('app', 'Rollover'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function afterDelete()
    {
        parent::afterDelete();
        Budget::deleteAll(['budget_config_id' => $this->id]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getBudgetProgress(): array
    {
        $now = Carbon::now()->toDateString();
        $budget = Budget::find()
            ->where(['budget_config_id' => $this->id])
            ->andWhere(['<=', 'started_at', $now])
            ->andWhere(['>=', 'ended_at', $now])
            ->asArray()
            ->all();

        $budgetAmountCent = array_sum(array_column($budget, 'budget_amount_cent'));
        $actualAmountCent = array_sum(array_column($budget, 'actual_amount_cent'));
        return [
            'started_at' => data_get($budget, '0.started_at'),
            'ended_at' => data_get($budget, '0.ended_at'),
            'actual_amount' => Setup::toYuan($actualAmountCent),
            'budget_amount' => Setup::toYuan($budgetAmountCent),
            'surplus_amount' => Setup::toYuan($budgetAmountCent - $actualAmountCent),
            'progress' => $budgetAmountCent ? bcmul(bcdiv($actualAmountCent, $budgetAmountCent, 4), 100, 1) : 100,
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id'], $fields['init_amount_cent'], $fields['amount_cent']);

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['init_amount'] = function (self $model) {
            return Setup::toYuan($model->init_amount_cent);
        };

        $fields['category_ids'] = function (self $model) {
            return $model->category_ids ? array_map('intval', explode(',', $model->category_ids)) : [];
        };

        $fields['category_ids_txt'] = function (self $model) {
            return $model->category_ids ?
                Category::find()->select('name')->where(['id' => explode(',', $model->category_ids)])->column()
                : '';
        };

        $fields['transaction_type'] = function (self $model) {
            return TransactionType::getName($model->transaction_type);
        };

        $fields['transaction_type_txt'] = function (self $model) {
            return data_get(TransactionType::texts(), $model->transaction_type);
        };

        $fields['period'] = function (self $model) {
            return BudgetPeriod::getName($model->period);
        };

        $fields['period_txt'] = function (self $model) {
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

        $fields['budgetProgress'] = function (self $model) {
            return $model->budgetProgress;
        };

        return $fields;
    }
}
