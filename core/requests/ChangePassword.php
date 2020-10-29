<?php

namespace app\core\requests;

use app\core\models\User;
use Yii;
use yii\base\Exception;
use yii\base\Model;

class ChangePassword extends Model
{
    public $old_password;
    public $new_password;
    public $retype_password;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_password', 'new_password', 'retype_password'], 'required'],
            [['old_password'], 'validatePassword'],
            [['new_password'], 'string', 'min' => 6],
            [['retype_password'], 'compare', 'compareAttribute' => 'new_password'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     */
    public function validatePassword()
    {
        /* @var $user User */
        $user = Yii::$app->user->identity;
        if (!$user || !$user->validatePassword($this->old_password)) {
            $this->addError('old_password', Yii::t('app', 'Incorrect old password.'));
        }
    }

    /**
     * Change password.
     * @return bool
     * @throws Exception
     */
    public function change(): bool
    {
        if ($this->validate()) {
            /* @var $user User */
            $user = Yii::$app->user->identity;
            $user->setPassword($this->new_password);
            $user->generateAuthKey();
            if ($user->save()) {
                return true;
            }
        }

        return false;
    }
}
