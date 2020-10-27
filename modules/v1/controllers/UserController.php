<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\User;
use app\core\requests\ChangePassword;
use app\core\requests\JoinRequest;
use app\core\requests\LoginRequest;
use app\core\requests\PasswordReset;
use app\core\requests\PasswordResetRequest;
use app\core\requests\PasswordResetTokenVerification;
use app\core\traits\ServiceTrait;
use Yii;
use yii\base\Exception;

/**
 * User controller for the `v1` module
 */
class UserController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = User::class;
    public $noAuthActions = ['join', 'login'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return User
     * @throws InvalidArgumentException
     * @throws \Throwable
     */
    public function actionJoin()
    {
        $params = Yii::$app->request->bodyParams;
        $data = $this->validate(new JoinRequest(), $params);
        return Yii::$app->db->transaction(function () use ($data) {
            /** @var JoinRequest $data */
            $user = $this->userService->createUser($data);
            if (params('verificationEmail')) {
                $this->mailerService->sendConfirmationMessage($user);
            }
            return $user;
        });
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException|\Throwable
     */
    public function actionLogin()
    {
        $params = Yii::$app->request->bodyParams;
        $this->validate(new LoginRequest(), $params);
        $token = $this->userService->getToken();
        $user = Yii::$app->user->identity;

        return [
            'user' => $user,
            'token' => (string)$token,
        ];
    }

    public function actionRefreshToken()
    {
        $user = Yii::$app->user->identity;
        $token = $this->userService->getToken();
        return [
            'user' => $user,
            'token' => (string)$token,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function actionResetToken()
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $this->userService->setPasswordResetToken($user);
        return [
            'reset_token' => $user->password_reset_token,
            'expire_in' => params('userPasswordResetTokenExpire')
        ];
    }

    /**
     * @return array
     */
    public function actionGetAuthClients()
    {
        return $this->userService->getAuthClients();
    }

    /**
     * Process password reset request
     *
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPasswordResetRequest()
    {
        $model = new PasswordResetRequest();
        /** @var PasswordResetRequest $model */
        $model = $this->validate($model, Yii::$app->request->bodyParams);
        return $this->userService->sendPasswordResetEmail($model);
    }

    /**
     * Verify password reset token
     * @return string
     * @throws InvalidArgumentException
     */
    public function actionPasswordResetTokenVerification()
    {
        $model = new PasswordResetTokenVerification();
        /** @var PasswordResetRequest $model */
        $this->validate($model, Yii::$app->request->bodyParams);
        return '';
    }


    /**
     * Process password reset
     * @return string
     * @throws InvalidArgumentException
     */
    public function actionPasswordReset()
    {
        $params = Yii::$app->request->bodyParams;
        $model = new PasswordReset();
        $model = $this->validate($model, $params);
        return $model->resetPassword();
    }


    /**
     * @return string
     * @throws Exception
     */
    public function actionChangePassword()
    {
        $params = Yii::$app->request->bodyParams;
        $model = new ChangePassword();
        /** @var ChangePassword $model */
        $model = $this->validate($model, $params);
        return $model->change();
    }
}
