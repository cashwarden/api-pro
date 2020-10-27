<?php

namespace app\core\requests;

use app\core\models\User;
use yii\base\Exception;
use yii\base\Model;

class PasswordReset extends Model
{
    public $token;
    public $password;

    /**
     * @var User
     */
    private $user;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['token', 'required'],
            ['token', 'validatePasswordResetToken'],
            ['password', 'required'],
            ['password', 'string', 'min' => 6],

        ];
    }

    /**
     * Validates the password reset token.
     * This method serves as the inline validation for password reset token.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePasswordResetToken(string $attribute, array $params)
    {
        $this->user = User::findByPasswordResetToken($this->$attribute);

        if (!$this->user) {
            $this->addError($attribute, 'Incorrect password reset token.');
        }
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     * @throws Exception
     */
    public function resetPassword()
    {
        $user = $this->user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
