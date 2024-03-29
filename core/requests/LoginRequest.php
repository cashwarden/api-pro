<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\requests;

use app\core\services\UserService;
use app\core\types\UserRole;
use Yii;

class LoginRequest extends \yii\base\Model
{
    public $username;
    public $code;
    public $password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'code'], 'filter', 'filter' => 'trim'],
            [['username', 'password'], 'required'],
            [['code'], 'string'],

            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = UserService::getUserByUsernameOrEmail($this->username);
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password.'));
            } elseif ($user->role === UserRole::ROLE_DISABLED) {
                $this->addError($attribute, Yii::t('app', 'Your account is disabled.'));
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
