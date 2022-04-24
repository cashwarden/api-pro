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
use app\core\services\TagService;
use app\core\services\TransactionService;
use app\core\types\ColorType;
use Yii;
use yii\behaviors\TimestampBehavior;
use yiier\helpers\DateHelper;

/**
 * This is the model class for table "{{%tag}}".
 *
 * @property int $id
 * @property int $ledger_id
 * @property int $user_id
 * @property string $color
 * @property string $name
 * @property int|null $count
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tag extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%tag}}';
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
            [['ledger_id', 'name'], 'required'],
            [['ledger_id', 'user_id', 'count'], 'integer'],
            [['color'], 'string', 'max' => 7],
            [['name'], 'string', 'max' => 60],
            [
                'ledger_id',
                'exist',
                'targetClass' => Ledger::class,
                'filter' => ['user_id' => Yii::$app->user->id],
                'targetAttribute' => 'id',
            ],
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
            'user_id' => Yii::t('app', 'User ID'),
            'ledger_id' => Yii::t('app', 'Ledger ID'),
            'color' => Yii::t('app', 'Color'),
            'name' => Yii::t('app', 'Name'),
            'count' => Yii::t('app', 'Count'),
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
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $rand = ColorType::items();
                $this->color = $this->color ?: $rand[mt_rand(0, count($rand) - 1)];
            }
            return true;
        }
        return false;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @throws \yii\db\Exception|\Throwable
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($oldName = data_get($changedAttributes, 'name', '')) {
            TagService::updateTagName($oldName, $this->name, $this->ledger_id);
        }
    }

    /**
     * @return bool
     * @throws CannotOperateException
     */
    public function beforeDelete()
    {
        if (TransactionService::countTransactionByTag($this->name, $this->ledger_id, $this->user_id)) {
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

        $fields['created_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->created_at);
        };

        $fields['updated_at'] = function (self $model) {
            return DateHelper::datetimeToIso8601($model->updated_at);
        };

        return $fields;
    }
}
