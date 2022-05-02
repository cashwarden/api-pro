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

/**
 * This is the model class for table "{{%member}}".
 *
 * @property int $id
 * @property int $parent_user_id
 * @property int $child_user_id
 * @property int $permission
 * @property int $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%member}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_user_id', 'child_user_id', 'permission', 'status'], 'required'],
            [['parent_user_id', 'child_user_id', 'permission', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['parent_user_id', 'child_user_id'], 'unique', 'targetAttribute' => ['parent_user_id', 'child_user_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_user_id' => Yii::t('app', 'Parent User ID'),
            'child_user_id' => Yii::t('app', 'Child User ID'),
            'permission' => Yii::t('app', 'Permission'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
