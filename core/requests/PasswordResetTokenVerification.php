<?php

namespace app\core\requests;

use app\core\models\User;
use yii\base\Model;

class PasswordResetTokenVerification extends Model
{
    public $token;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['token', 'required'],
            ['token', 'validatePasswordResetToken'],
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
        $user = User::findByPasswordResetToken($this->$attribute);

        if (!$user) {
            $this->addError($attribute, \Yii::t('app', 'Incorrect password reset token.'));
        }
    }
}
