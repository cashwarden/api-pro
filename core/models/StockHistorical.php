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

use Yii;

/**
 * This is the model class for table "{{%stock_historical}}".
 *
 * @property int $id
 * @property string $code
 * @property int $open_price_cent
 * @property int $current_price_cent
 * @property int $change_price_cent
 * @property string $date
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class StockHistorical extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stock_historical}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'open_price_cent', 'current_price_cent', 'change_price_cent'], 'required'],
            [['open_price_cent', 'current_price_cent', 'change_price_cent'], 'integer'],
            [['date', 'created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 10],
            [['code', 'date'], 'unique', 'targetAttribute' => ['code', 'date']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'open_price_cent' => Yii::t('app', 'Open Price Cent'),
            'current_price_cent' => Yii::t('app', 'Current Price Cent'),
            'change_price_cent' => Yii::t('app', 'Change Price Cent'),
            'date' => Yii::t('app', 'Date'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
