<?php

namespace app\core\requests;

use app\core\services\UserService;
use app\core\types\UserStatus;
use common\models\User;
use Yii;

class LoginRequest extends \yii\base\Model
{
    public $username;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'], 'filter', 'filter' => 'trim'],
            [['username', 'password'], 'required'],

            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = UserService::getUserByUsernameOrEmail($this->username);
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password.'));
            }
            if ($user['status'] === UserStatus::UNACTIVATED) {
                $this->addError($attribute, Yii::t('app', 'Please email to activate your account first.'));
            }
            Yii::$app->user->setIdentity($user);
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => t('app', 'Username'),
            'password' => t('app', 'Password'),
            'email' => t('app', 'Email'),
        ];
    }
}
