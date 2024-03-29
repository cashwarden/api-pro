<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\modules\backend\models;

use app\core\models\User;
use yii\base\Model;

class UpgradeProForm extends Model
{
    public ?string $date = null;
    public int $user_id;
    public ?string $username;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['user_id', 'date'], 'required'],
            ['username', 'string'],
//            ['date', 'date'],
            ['user_id', 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => '用户 ID',
            'username' => '用户',
            'date' => '会员到期时间',
        ];
    }
}
