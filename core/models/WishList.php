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

use app\core\types\WishListStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\helpers\Setup;
use yiier\validators\MoneyValidator;

/**
 * This is the model class for table "{{%wish_list}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $ledger_id
 * @property string $name
 * @property int $amount_cent
 * @property int $currency_amount_cent
 * @property string $currency_code
 * @property string|null $remark
 * @property int|null $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class WishList extends \yii\db\ActiveRecord
{
    /**
     * @var float
     */
    public $amount;

    /**
     * @var float
     */
    public $currency_amount;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%wish_list}}';
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
            [['ledger_id', 'name', 'currency_amount', 'currency_code'], 'required'],
            [['user_id', 'ledger_id', 'amount_cent', 'currency_amount_cent'], 'integer'],
            [
                'ledger_id',
                'exist',
                'targetClass' => LedgerMember::class,
                'filter' => ['user_id' => Yii::$app->user->id],
            ],
            [['amount', 'currency_amount'], MoneyValidator::class], //todo message
            ['status', 'in', 'range' => WishListStatus::names()],
            [['remark'], 'string'],
            [['name'], 'string', 'max' => 255],
            [['currency_code'], 'string', 'max' => 3],
        ];
    }

    /**
     * @param bool $insert
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
            $this->status = $this->status === null ?
                WishListStatus::TODO : WishListStatus::toEnumValue($this->status);

            $this->currency_amount_cent = Setup::toFen($this->currency_amount);
            if ($this->currency_code == Yii::$app->user->identity['base_currency_code']) {
                $this->amount_cent = $this->currency_amount_cent;
            }
            // $this->amount_cent = $this->currency_amount_cent;
            // todo 计算汇率

            return true;
        }
        return false;
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
            'name' => Yii::t('app', 'Name'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'currency_amount_cent' => Yii::t('app', 'Currency Amount Cent'),
            'currency_code' => Yii::t('app', 'Currency Code'),
            'remark' => Yii::t('app', 'Remark'),
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
        unset($fields['currency_amount_cent'], $fields['user_id'], $fields['amount_cent']);

        $fields['currency_amount'] = function (self $model) {
            return Setup::toYuan($model->currency_amount_cent);
        };

        $fields['amount'] = function (self $model) {
            return Setup::toYuan($model->amount_cent);
        };

        $fields['status_txt'] = function (self $model) {
            return data_get(WishListStatus::texts(), $model->status);
        };

        $fields['status'] = function (self $model) {
            return WishListStatus::getName($model->status);
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
