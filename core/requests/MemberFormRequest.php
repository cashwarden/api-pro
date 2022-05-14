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
use app\core\types\UserRole;

class MemberFormRequest extends \yii\base\Model
{
    public $id;
    public $username;
    public $email;
    public $password;
    public $role;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required', 'on' => 'create'],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'filter' => function ($query) {
                    if ($this->id) {
                        $query->andWhere(['!=', 'id', $this->id]);
                    }
                },
            ],
            ['username', 'string', 'min' => 3, 'max' => 60],

            ['email', 'string', 'min' => 2, 'max' => 120],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'filter' => function ($query) {
                    if ($this->id) {
                        $query->andWhere(['!=', 'id', $this->id]);
                    }
                },
            ],
            ['email', 'email'],

            ['password', 'required', 'on' => 'create'],
            ['password', 'string', 'min' => 6],

            [
                'role',
                'in',
                'range' => [
                    UserRole::names()[UserRole::ROLE_DISABLED],
                    UserRole::names()[UserRole::ROLE_READ_ONLY],
                    UserRole::names()[UserRole::ROLE_READ_WRITE],
                ],
            ],
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
            'role' => t('app', 'Role'),
        ];
    }
}
