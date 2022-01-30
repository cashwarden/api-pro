<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://github.com/cashwarden
 * @copyright Copyright (c) 2019 - 2022 forecho
 * @license https://github.com/cashwarden/api-pro/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\requests;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\User;
use app\core\types\UserStatus;
use yii\base\Model;

class JoinConfirm extends Model
{
    public $token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['token', 'trim'],
            ['token', 'required'],
            [
                'token',
                'exist',
                'targetClass' => User::class,
                'targetAttribute' => 'password_reset_token',
                'message' => \Yii::t('app', 'The Token is not valid.'),
            ],
        ];
    }


    /**
     * @return User|array|\yii\db\ActiveRecord|null
     * @throws InvalidArgumentException
     */
    public function confirm()
    {
        if (!$user = User::findByPasswordResetToken($this->token)) {
            throw new InvalidArgumentException(
                \Yii::t('app', 'The link is invalid or has expired, please try again.')
            );
        }
        $user->status = UserStatus::ACTIVE;
        $user->password_reset_token = '';
        $user->save();
        return $user;
    }
}
