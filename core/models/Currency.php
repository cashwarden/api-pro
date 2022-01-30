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
use app\core\types\CurrencyType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%currency}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $ledger_id
 * @property string $currency_code_from
 * @property string $currency_code_to
 * @property int|null $rate
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read Ledger $ledger
 */
class Currency extends \yii\db\ActiveRecord
{
    public const RATE_MULTIPLE = 10000000;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%currency}}';
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
            [['user_id', 'ledger_id', 'currency_code_from', 'currency_code_to'], 'required'],
            [['user_id', 'ledger_id', 'rate', 'status'], 'integer'],
            [
                'ledger_id',
                'exist',
                'targetClass' => Ledger::class,
                'filter' => ['user_id' => Yii::$app->user->id],
                'targetAttribute' => 'id',
            ],
            [['currency_code_from', 'currency_code_to'], 'in', 'range' => CurrencyType::currentUseCodes()],
            ['currency_code_from', 'compare', 'compareAttribute' => 'currency_code_to', 'operator' => '!='],
            [
                ['ledger_id', 'currency_code_from', 'currency_code_to'],
                'unique',
                'targetAttribute' => ['ledger_id', 'currency_code_from', 'currency_code_to'],
            ],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user_id = Yii::$app->user->id;
            if (!$this->rate) {
                $this->addError('rate', '汇率必须大于0');
            }
            if ($this->rate && is_numeric($this->rate)) {
                $this->rate = bcmul($this->rate, self::RATE_MULTIPLE);
            }
            return true;
        }
        return false;
    }

    /**
     * @param bool $insert
     * @return bool
     * @throws InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->currency_code_to != $this->ledger->base_currency_code) {
                throw new InvalidArgumentException('currency_code_from 参数错误');
            }
            return true;
        }
        return false;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->rate = bcdiv($this->rate, self::RATE_MULTIPLE, 7);
    }


    public function getLedger()
    {
        return $this->hasOne(Ledger::class, ['id' => 'ledger_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'ledger_id' => Yii::t('app', 'Ledger ID'),
            'currency_code_from' => Yii::t('app', 'Base Currency Code'),
            'currency_code_to' => Yii::t('app', 'Currency'),
            'rate' => Yii::t('app', 'Rate'),
            'status' => Yii::t('app', 'Status'),
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
        unset($fields['user_id']);

        $fields['currency_code_to_name'] = function (self $model) {
            return data_get(CurrencyType::names(), $model->currency_code_to);
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
