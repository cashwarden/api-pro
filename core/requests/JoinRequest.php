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

use app\core\models\User;
use app\core\types\CurrencyType;

class JoinRequest extends \yii\base\Model
{
    public string $username;
    public string $email;
    public string $password;
    public string $base_currency_code;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email', 'base_currency_code'], 'required'],

            ['username', 'unique', 'targetClass' => User::class],
            ['username', 'string', 'min' => 3, 'max' => 60],

            ['email', 'string', 'min' => 2, 'max' => 120],
            ['email', 'unique', 'targetClass' => User::class],
            ['email', 'email'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],

            ['base_currency_code', 'in', 'range' => CurrencyType::currentUseCodes()],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'username' => t('app', 'Username'),
            'password' => t('app', 'Password'),
            'email' => t('app', 'Email'),
            'base_currency_code' => t('app', 'Base Currency Code'),
        ];
    }
}
