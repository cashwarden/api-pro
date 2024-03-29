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

use app\core\jobs\UpdateBudgetJob;
use app\core\services\TagService;
use app\core\services\TransactionService;
use app\core\types\RecordSource;
use app\core\types\ReimbursementStatus;
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
 * This is the model class for table "{{%transaction}}".
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $user_id
 * @property int $from_account_id
 * @property int|null $to_account_id
 * @property int $type
 * @property int $category_id
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property string|array $tags Multiple choice use,
 * @property string|null $description
 * @property string|null $remark
 * @property string|null $image
 * @property int|null $status
 * @property int|null $rating
 * @property string $date
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read Category $category
 * @property-read Account $fromAccount
 * @property-read Record[] $records
 * @property-read Ledger $ledger
 * @property-read Account $toAccount
 */
class Transaction extends \yii\db\ActiveRecord
{
    /**
     * @var int
     */
    public $amount;

    /**
     * @var string
     */
    public $source;

    /**
     * @var bool
     */
    public $exclude_from_stats;

    /**
     * @var string
     */
    public $reimbursement_status;

    /**
     * @var int
     */
    public $currency_amount;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%transaction}}';
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            TransactionType::getName(TransactionType::INCOME) => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            TransactionType::getName(TransactionType::EXPENSE) => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
            TransactionType::getName(TransactionType::TRANSFER) => self::OP_INSERT | self::OP_UPDATE | self::OP_DELETE,
        ];
    }


    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->scenario = $this->type;
            return true;
        }
        return false;
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
            [['ledger_id', 'type', 'category_id', 'currency_amount', 'currency_code'], 'required'],
            [
                'to_account_id',
                'required',
                'on' => [
                    TransactionType::getName(TransactionType::INCOME),
                    TransactionType::getName(TransactionType::TRANSFER),
                ],
            ],
            [
                'from_account_id',
                'required',
                'on' => [
                    TransactionType::getName(TransactionType::EXPENSE),
                    TransactionType::getName(TransactionType::TRANSFER),
                ],
            ],
            [
                [
                    'ledger_id',
                    'user_id',
                    'from_account_id',
                    'to_account_id',
                    'category_id',
                    'amount_cent',
                    'currency_amount_cent',
                    'rating',
                ],
                'integer',
            ],
            ['ledger_id', LedgerIdValidator::class],
            [['description', 'remark'], 'trim'],
            ['type', 'in', 'range' => TransactionType::names()],
            ['reimbursement_status', 'in', 'range' => ReimbursementStatus::names()],
            ['status', 'in', 'range' => TransactionStatus::names()],
            [
                'currency_amount',
                'compare',
                'compareValue' => 0,
                'operator' => '>',
                'message' => Yii::t('app', 'Accounting failed, the amount must be greater than 0.'),
            ],
            [['amount', 'currency_amount'], MoneyValidator::class], //todo message

            [['date'], 'datetime', 'format' => 'php:Y-m-d H:i'],
            [['currency_code'], 'string', 'max' => 3],
            [['description', 'remark', 'image'], 'string', 'max' => 255],
            ['tags', ArrayValidator::class],
            ['source', 'in', 'range' => array_keys(RecordSource::names())],
            ['exclude_from_stats', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'ledger_id' => Yii::t('app', 'Ledger Id'),
            'user_id' => Yii::t('app', 'User ID'),
            'from_account_id' => Yii::t('app', 'From Account ID'),
            'to_account_id' => Yii::t('app', 'To Account ID'),
            'type' => Yii::t('app', 'Type'),
            'category_id' => Yii::t('app', 'Category ID'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_amount' => Yii::t('app', 'Currency Amount'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'tags' => Yii::t('app', 'Tags'),
            'description' => Yii::t('app', 'Description'),
            'remark' => Yii::t('app', 'Remark'),
            'image' => Yii::t('app', 'Image'),
            'status' => Yii::t('app', 'Status'),
            'rating' => Yii::t('app', 'Rating'),
            'date' => Yii::t('app', 'Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * @param  bool  $insert
     * @return bool
     * @throws \Throwable
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->user_id = Yii::$app->user->id;
            }
            $this->reimbursement_status = $this->reimbursement_status === null ?
                ReimbursementStatus::NONE : ReimbursementStatus::toEnumValue($this->reimbursement_status);
            $this->status = $this->status === null ?
                TransactionStatus::DONE : TransactionStatus::toEnumValue($this->status);
            $this->type = TransactionType::toEnumValue($this->type);

            $this->currency_amount_cent = Setup::toFen($this->currency_amount);
            if ($this->currency_code == $this->ledger->base_currency_code) {
                $this->amount_cent = $this->currency_amount_cent;
            } else {
                $rate = TransactionService::getRate($this, $this->ledger->base_currency_code);
                $this->amount_cent = Setup::toFen($rate * $this->currency_amount);
            }
            $this->tags ? TransactionService::createTags($this->tags, $this->ledger_id) : null;
            if ($this->description) {
                $this->tags = array_merge(
                    (array) $this->tags,
                    TransactionService::matchTagsByDesc($this->description, $this->ledger_id)
                );
            }

            $this->tags = $this->tags ? implode(',', array_unique($this->tags)) : null;
            return true;
        }
        return false;
    }


    /**
     * @param  bool  $insert
     * @param  array  $changedAttributes
     * @return false
     * @throws \yii\db\Exception|\Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!$insert) {
            TransactionService::deleteRecord($this, $changedAttributes);
        }
        TransactionService::createUpdateRecord($this);
        $oldTags = data_get($changedAttributes, 'tags', '');

        $tags = explode(',', $this->tags) + explode(',', $oldTags);
        if ($tags = array_unique($tags)) {
            TagService::updateCounters($tags, $this->ledger_id);
        }
        if ($date = data_get($changedAttributes, 'date')) {
            Yii::$app->queue->push(new UpdateBudgetJob(['ledgerId' => $this->ledger_id, 'datetime' => $date]));
        }
        Yii::$app->queue->push(new UpdateBudgetJob(['ledgerId' => $this->ledger_id, 'datetime' => $this->date]));
        if (params('useXunSearch')) {
            Search::createUpdate($insert, $this);
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if (params('useXunSearch')) {
            Search::deleteAll(['id' => $this->id]);
        }
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getFromAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'from_account_id']);
    }

    public function getRecords()
    {
        return $this->hasMany(Record::class, ['transaction_id' => 'id']);
    }

    public function getToAccount()
    {
        return $this->hasOne(Account::class, ['id' => 'to_account_id']);
    }

    public function getLedger()
    {
        return $this->hasOne(Ledger::class, ['id' => 'ledger_id']);
    }

    public function extraFields()
    {
        return ['toAccount', 'fromAccount', 'category', 'ledger'];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['currency_amount_cent'], $fields['user_id'], $fields['amount_cent']);

        $fields['currency_amount'] = function (self $model) {
            return Setup::toYuan($model->currency_amount_cent);
        };

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['type'] = function (self $model) {
            return TransactionType::getName($model->type);
        };

        $fields['type_text'] = function (self $model) {
            return data_get(TransactionType::texts(), $model->type);
        };

        $fields['tags'] = function (self $model) {
            return $model->tags ? explode(',', $model->tags) : [];
        };

        $fields['status'] = function (self $model) {
            return TransactionStatus::getName($model->status);
        };

        $fields['date'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->date);
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
