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
     */
    public function validatePasswordResetToken(string $attribute)
    {
        $this->user = User::findByPasswordResetToken($this->$attribute);

        if (!$this->user) {
            $this->addError($attribute, \Yii::t('app', 'Incorrect password reset token.'));
        }
    }

    /**
     * Resets password.
     *
     * @return bool if password was reset.
     * @throws Exception
     */
    public function resetPassword(): bool
    {
        $user = $this->user;
        $user->setPassword($this->password);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
