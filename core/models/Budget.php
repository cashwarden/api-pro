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

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%budget}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $budget_config_id
 * @property int $budget_amount_cent
 * @property int $actual_amount_cent
 * @property string|null $record_ids
 * @property int|null $relation_budget_id
 * @property string|null $started_at
 * @property string|null $ended_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Budget extends \yii\db\ActiveRecord
{
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
            [['user_id', 'budget_config_id', 'budget_amount_cent', 'actual_amount_cent'], 'required'],
            [
                ['user_id', 'budget_config_id', 'budget_amount_cent', 'actual_amount_cent', 'relation_budget_id'],
                'integer',
            ],
            [['record_ids'], 'string'],
            [['started_at', 'ended_at', 'created_at', 'updated_at'], 'safe'],
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
            'budget_config_id' => Yii::t('app', 'Budget Config ID'),
            'budget_amount_cent' => Yii::t('app', 'Budget Amount Cent'),
            'actual_amount_cent' => Yii::t('app', 'Actual Amount Cent'),
            'record_ids' => Yii::t('app', 'Record Ids'),
            'relation_budget_id' => Yii::t('app', 'Relation Budget ID'),
            'started_at' => Yii::t('app', 'Started At'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
