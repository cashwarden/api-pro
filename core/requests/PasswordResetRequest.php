<?php

namespace app\core\requests;

use app\core\models\User;
use Yii;
use yii\base\Model;

class PasswordResetRequest extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email',
                'exist',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'There is no user with this email address.')
            ],
        ];
    }
}
