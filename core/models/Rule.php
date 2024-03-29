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
use app\core\types\ReimbursementStatus;
use app\core\types\RuleStatus;
use app\core\types\TransactionStatus;
use app\core\types\TransactionType;
use app\core\validators\LedgerIdValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\ArrayValidator;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%rule}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|array $if_keywords Multiple choice use,
 * @property int $ledger_id
 * @property int $then_transaction_type
 * @property int|null $then_category_id
 * @property int|null $then_from_account_id
 * @property int|null $then_to_account_id
 * @property int|null $then_currency_amount_cent
 * @property string|null $then_currency_code
 * @property int|null $then_transaction_status
 * @property int|null $then_reimbursement_status
 * @property string|null|array $then_tags Multiple choice use,
 * @property int|string $status
 * @property int|null $sort
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Ledger $ledger
 * @property-read Category $thenCategory
 */
class Rule extends \yii\db\ActiveRecord
{
    /**
     * @var int
     */
    public $then_currency_amount;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%rule}}';
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
            [['name', 'if_keywords', 'then_transaction_type'], 'required'],
            [
                ['user_id', 'ledger_id', 'then_category_id', 'then_from_account_id', 'then_to_account_id', 'sort'],
                'integer',
            ],
            ['ledger_id', LedgerIdValidator::class],
            ['status', 'in', 'range' => RuleStatus::names()],
            ['then_transaction_type', 'in', 'range' => TransactionType::names()],
            ['then_reimbursement_status', 'in', 'range' => ReimbursementStatus::names()],
            ['then_transaction_status', 'in', 'range' => TransactionStatus::names()],
            [['if_keywords', 'then_tags'], ArrayValidator::class],
            [['name'], 'string', 'max' => 255],
            [['then_currency_amount'], MoneyValidator::class], //todo message
            [['then_currency_code'], 'string', 'max' => 3],
        ];
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
            'if_keywords' => Yii::t('app', 'If Keywords'),
            'ledger_id' => Yii::t('app', 'Then Ledger ID'),
            'then_transaction_type' => Yii::t('app', 'Then Transaction Type'),
            'then_category_id' => Yii::t('app', 'Then Category ID'),
            'then_from_account_id' => Yii::t('app', 'Then From Account ID'),
            'then_to_account_id' => Yii::t('app', 'Then To Account ID'),
            'then_currency_amount_cent' => Yii::t('app', 'Then Currency Amount Cent'),
            'then_currency_amount' => Yii::t('app', 'Then Currency Amount'),
            'then_currency_code' => Yii::t('app', 'Then Currency Code'),
            'then_transaction_status' => Yii::t('app', 'Then Transaction Status'),
            'then_reimbursement_status' => Yii::t('app', 'Then Reimbursement Status'),
            'then_tags' => Yii::t('app', 'Then Tags'),
            'status' => Yii::t('app', 'Status'),
            'sort' => Yii::t('app', 'Sort'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param  bool  $insert
     * @return bool
     * @throws InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->then_reimbursement_status = $this->then_reimbursement_status === null ?
                ReimbursementStatus::NONE : ReimbursementStatus::toEnumValue($this->then_reimbursement_status);
            $this->then_transaction_status = $this->then_transaction_status === null ?
                TransactionStatus::DONE : TransactionStatus::toEnumValue($this->then_transaction_status);

            $this->then_currency_amount_cent = Setup::toFen($this->then_currency_amount);
            $this->status = $this->status === null ? RuleStatus::ACTIVE : RuleStatus::toEnumValue($this->status);
            $this->then_transaction_type = TransactionType::toEnumValue($this->then_transaction_type);
            $this->if_keywords = $this->if_keywords ? implode(',', $this->if_keywords) : null;
            $this->then_tags = $this->then_tags ? implode(',', $this->then_tags) : null;
            return true;
        }
        return false;
    }

    public function getThenCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'then_category_id']);
    }

    public function getLedger()
    {
        return $this->hasOne(Ledger::class, ['id' => 'ledger_id']);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id'], $fields['then_currency_amount']);


        $fields['ledger'] = function (self $model) {
            return $model->ledger;
        };

        $fields['then_currency_amount'] = function (self $model) {
            return Setup::toYuan($model->then_currency_amount_cent);
        };

        $fields['then_transaction_type'] = function (self $model) {
            return TransactionType::getName($model->then_transaction_type);
        };

        $fields['then_transaction_type_text'] = function (self $model) {
            return data_get(TransactionType::texts(), $model->then_transaction_type);
        };

        $fields['then_tags'] = function (self $model) {
            return $model->then_tags ? explode(',', $model->then_tags) : [];
        };

        $fields['thenCategory'] = function (self $model) {
            return $model->thenCategory;
        };

        $fields['if_keywords'] = function (self $model) {
            return explode(',', $model->if_keywords);
        };

        $fields['status'] = function (self $model) {
            return RuleStatus::getName($model->status);
        };

        $fields['then_reimbursement_status'] = function (self $model) {
            return ReimbursementStatus::getName($model->then_reimbursement_status);
        };

        $fields['then_transaction_status'] = function (self $model) {
            return TransactionStatus::getName($model->then_transaction_status);
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
