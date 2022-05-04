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

use app\core\exceptions\CannotOperateException;
use app\core\exceptions\InvalidArgumentException;
use app\core\services\TransactionService;
use app\core\types\ColorType;
use app\core\types\TransactionType;
use app\core\validators\LedgerIdValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;
use yiier\validators\ArrayValidator;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $user_id
 * @property int $transaction_type
 * @property string $name
 * @property string|null|array $keywords
 * @property string $color
 * @property string $icon_name
 * @property int|null $status
 * @property int $default
 * @property int|null $sort
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Category extends \yii\db\ActiveRecord
{
    public const DEFAULT = 1;
    public const NOT_DEFAULT = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%category}}';
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
            [['ledger_id', 'transaction_type', 'name', 'icon_name'], 'required'],
            [['ledger_id', 'user_id', 'status', 'sort'], 'integer'],
            ['transaction_type', 'in', 'range' => TransactionType::names()],
            [['name', 'icon_name'], 'string', 'max' => 120],
            ['color', 'in', 'range' => ColorType::items()],
            ['default', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            [['keywords'], ArrayValidator::class],
            ['ledger_id', LedgerIdValidator::class],
            [
                'name',
                'unique',
                'targetAttribute' => ['user_id', 'ledger_id', 'name'],
                'message' => Yii::t('app', 'The {attribute} has been used.'),
            ],
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
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'name' => Yii::t('app', 'Name'),
            'keywords' => Yii::t('app', 'Keywords'),
            'color' => Yii::t('app', 'Color'),
            'icon_name' => Yii::t('app', 'Icon Name'),
            'status' => Yii::t('app', 'Status'),
            'default' => Yii::t('app', 'Default'),
            'sort' => Yii::t('app', 'Sort'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
     * @param  bool  $insert
     * @return bool
     * @throws InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $ran = ColorType::items();
                $this->color = $this->color ?: $ran[mt_rand(0, count($ran) - 1)];
            }
            $this->keywords = $this->keywords ? implode(',', $this->keywords) : null;
            $this->transaction_type = TransactionType::toEnumValue($this->transaction_type);
            return true;
        }
        return false;
    }

    /**
     * @param  bool  $insert
     * @param  array  $changedAttributes
     * @throws \Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($this->default) {
            self::updateAll(
                ['default' => self::NOT_DEFAULT, 'updated_at' => Yii::$app->formatter->asDatetime('now')],
                [
                    'and',
                    [
                        'ledger_id' => $this->ledger_id,
                        'default' => self::DEFAULT,
                        'transaction_type' => $this->transaction_type,
                    ],
                    ['!=', 'id', $this->id],
                ]
            );
        }
    }

    /**
     * @return bool
     * @throws CannotOperateException
     */
    public function beforeDelete()
    {
        if (TransactionService::countTransactionByCategoryId($this->id, $this->ledger_id, $this->user_id)) {
            throw new CannotOperateException(Yii::t('app', 'Cannot be deleted because it has been used.'));
        }
        return parent::beforeDelete();
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id']);

        $fields['keywords'] = function (self $model) {
            return $model->keywords ? explode(',', $model->keywords) : [];
        };

        $fields['transaction_type'] = function (self $model) {
            return TransactionType::getName($model->transaction_type);
        };

        $fields['transaction_type_text'] = function (self $model) {
            return data_get(TransactionType::texts(), $model->transaction_type);
        };

        $fields['default'] = function (self $model) {
            return (bool) $model->default;
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
