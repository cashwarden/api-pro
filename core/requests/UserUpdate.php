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

use app\core\models\User;
use app\core\types\UserStatus;
use Yii;
use yii\base\Model;
use yii\db\Exception;
use yiier\helpers\Setup;

class UserUpdate extends Model
{
    public $id;
    public $username;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['id', 'exist', 'targetClass' => User::class],
            [['email', 'username'], 'trim'],
            ['username', 'string', 'min' => 3, 'max' => 60],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'This username has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['!=', 'id', $this->id]);
                },
            ],

            ['email', 'string', 'min' => 2, 'max' => 120],
            ['email', 'email'],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'This email address has already been taken.'),
                'filter' => function ($query) {
                    $query->andWhere(['!=', 'id', $this->id]);
                },
            ],
        ];
    }

    /**
     * Signs user up.
     * @return User
     * @throws \yii\base\Exception
     * @throws \Exception
     */
    public function save()
    {
        /** @var User $user */
        $user = User::find()->where(['id' => $this->id])->one();
        $user->username = $this->username;
        if ($this->email && $user->email !== $this->email) {
            $user->email = $this->email;
            $user->status = UserStatus::UNACTIVATED;
        }
        if (!$user->save()) {
            throw new Exception(Setup::errorMessage($user->firstErrors));
        }

//        if ($this->emailChanged && params('verificationEmail')) {
//            try {
//                (new MailerService())->sendConfirmationMessage($user);
//            } catch (\Exception $e) {
//                throw new \Exception('您的邮箱地址有误，请重新输入');
//            }
//        }
        return $user;
    }
}
