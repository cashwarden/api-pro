<?php

namespace app\core\models;

use app\core\helpers\FormatFactory;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%user_pro_record}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $out_sn 流水号
 * @property int $source 来源：1系统授予 2购买 3邀请
 * @property int $amount_cent
 * @property int|null $status 状态：1审核通过 0待审核
 * @property string|null $remark 备注
 * @property string|null $ended_at
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class UserProRecord extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_pro_record}}';
    }

    public static function makeOrderNo(): string
    {
        $m = microtime(true);
        $no = sprintf("%8x%05x", floor($m), ($m - floor($m)) * 1000000);
        return strtoupper($no);
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
            [['user_id', 'out_sn', 'source', 'amount_cent', 'ended_at'], 'required'],
            [['user_id', 'source', 'amount_cent', 'status'], 'integer'],
            [['ended_at', 'created_at', 'updated_at'], 'safe'],
            [['out_sn'], 'string', 'max' => 20],
            [['remark'], 'string', 'max' => 255],
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
            'out_sn' => Yii::t('app', 'Out Sn'),
            'source' => Yii::t('app', 'Source'),
            'amount_cent' => Yii::t('app', 'Amount Cent'),
            'status' => Yii::t('app', 'Status'),
            'remark' => Yii::t('app', 'Remark'),
            'ended_at' => Yii::t('app', 'Ended At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            return true;
        } else {
            return false;
        }
    }
}
