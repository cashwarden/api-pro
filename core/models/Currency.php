<?php

namespace app\core\models;

use app\core\exceptions\InvalidArgumentException;
use app\core\types\CurrencyType;
use Yii;
use yii\behaviors\TimestampBehavior;

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
            [['user_id', 'ledger_id', 'currency_code_from', 'currency_code_to'], 'required'],
            [['user_id', 'ledger_id', 'rate', 'status'], 'integer'],
            [
                'ledger_id',
                'exist',
                'targetClass' => Ledger::class,
                'filter' => ['user_id' => Yii::$app->user->id],
                'targetAttribute' => 'id',
            ],
            [['created_at', 'updated_at'], 'safe'],
            [['currency_code_from', 'currency_code_to'], 'string', 'max' => 3],
            [['currency_code_from', 'currency_code_to'], 'in', 'range' => CurrencyType::currentUseCodes()],
            ['currency_code_from', 'compare', 'compareAttribute' => 'currency_code_to', 'operator' => '!='],
        ];
    }

    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user_id = Yii::$app->user->id;
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
            if ($this->currency_code_from != $this->ledger->base_currency_code) {
                throw new InvalidArgumentException('currency_code_from 参数错误');
            }
            return true;
        } else {
            return false;
        }
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
            'currency_code_from' => Yii::t('app', 'Currency Code From'),
            'currency_code_to' => Yii::t('app', 'Currency Code To'),
            'rate' => Yii::t('app', 'Rate'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
