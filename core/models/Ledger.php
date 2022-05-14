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
use app\core\services\LedgerService;
use app\core\types\CurrencyType;
use app\core\types\LedgerType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\ForbiddenHttpException;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%ledger}}".
 *
 * @property int $id
 * @property string $name
 * @property int|string $type 类型
 * @property int $user_id
 * @property string $base_currency_code
 * @property string|null $cover
 * @property string|null $remark
 * @property int|null $default
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-read User $user
 * @property-read Category[] $categories
 */
class Ledger extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%ledger}}';
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


    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $this->user_id = $this->user_id ?: Yii::$app->user->id;
            return true;
        }
        return false;
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'user_id', 'type'], 'required'],
            [['user_id'], 'integer'],
            ['type', 'in', 'range' => LedgerType::names()],
            [['base_currency_code'], 'string', 'max' => 3],
            [['name'], 'string', 'max' => 100],
            [['cover', 'remark'], 'string', 'max' => 255],
            ['default', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'type' => Yii::t('app', 'Type'),
            'user_id' => Yii::t('app', 'User ID'),
            'base_currency_code' => Yii::t('app', 'Base Currency Code'),
            'cover' => Yii::t('app', 'Cover'),
            'remark' => Yii::t('app', 'Remark'),
            'default' => Yii::t('app', 'Default'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }


    /**
     * @param  bool  $insert
     * @return bool
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->base_currency_code = $this->base_currency_code ?: CurrencyType::CNY_KEY;
            $this->type = LedgerType::toEnumValue($this->type);
            // 有记录时不支持修改基础货币
            if (!$insert && Transaction::find()->where(['ledger_id' => $this->id])->count('id')) {
                if (data_get($this->oldAttributes, 'base_currency_code') != $this->base_currency_code) {
                    throw new InvalidArgumentException('账本有数据之后不再支持修改基础货币');
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @param  bool  $insert
     * @param  array  $changedAttributes
     * @throws \yii\db\Exception|\Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            LedgerService::createLedgerAfter($this);
        }
    }

    /**
     * @return bool
     * @throws CannotOperateException|ForbiddenHttpException
     */
    public function beforeDelete()
    {
        if ($this->default) {
            throw new CannotOperateException(Yii::t('app', 'Cannot delete the default ledger.'));
        }
        if ($this->user_id !== Yii::$app->user->id) {
            throw new ForbiddenHttpException(Yii::t('app', 'You do not have permission to operate.'));
        }
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        parent::afterDelete();
        LedgerService::afterDelete($this->id);
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['ledger_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function extraFields()
    {
        return ['user'];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['user_id']);

        $fields['type'] = function (self $model) {
            return LedgerType::getName($model->type);
        };

        $fields['type_name'] = function (self $model) {
            return data_get(LedgerType::texts(), $model->type);
        };

        $fields['default'] = function (self $model) {
            return (bool) $model->default;
        };

        $fields['creator'] = function (self $model) {
            return (bool) ($model->user_id == Yii::$app->user->id);
        };

        $fields['created_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->created_at);
        };

        $fields['updated_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->updated_at);
        };

        $fields['hash_id'] = function (self $model) {
            return Yii::$app->hashids->encode($model->id);
        };

        return $fields;
    }
}
