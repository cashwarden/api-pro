<?php

namespace app\modules\v1\controllers;

use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\models\User;
use app\core\requests\ChangePassword;
use app\core\requests\JoinConfirm;
use app\core\requests\JoinRequest;
use app\core\requests\LoginRequest;
use app\core\requests\PasswordReset;
use app\core\requests\PasswordResetRequest;
use app\core\requests\PasswordResetTokenVerification;
use app\core\requests\UserUpdate;
use app\core\services\LedgerService;
use app\core\services\UserService;
use app\core\traits\ServiceTrait;
use app\core\types\UserSettingKeys;
use app\core\types\UserStatus;
use Yii;
use yii\base\Exception;
use yiier\helpers\Setup;

/**
 * User controller for the `v1` module
 */
class UserController extends ActiveController
{
    use ServiceTrait;

    public $modelClass = User::class;
    public $noAuthActions = ['join', 'login', 'confirm', 'password-reset-request', 'password-reset', 'options'];

    public function actions()
    {
        $actions = parent::actions();
        // 注销系统自带的实现方法
        unset($actions['update'], $actions['index'], $actions['delete'], $actions['create']);
        return $actions;
    }

    /**
     * @return User
     * @throws InvalidArgumentException|\Throwable
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
            Yii::$app->user->setIdentity($user);
            $token = $this->userService->getToken();
            return [
                'user' => $user,
                'token' => (string)$token,
                'default_ledger' => LedgerService::getDefaultLedger($user->id),
            ];
        });
    }

    /**
     * @return string[]
     * @throws InvalidArgumentException|\Throwable
     */
    public function actionLogin(): array
    {
        $params = Yii::$app->request->bodyParams;
        /** @var LoginRequest $data */
        $data = $this->validate(new LoginRequest(), $params);
        $token = $this->userService->getToken();
        $user = Yii::$app->user->identity;
        if ($data->code) {
            $this->wechatService->bind(Yii::$app->user->id, $data->code);
        }

        return [
            'user' => $user,
            'token' => (string)$token,
            'default_ledger' => LedgerService::getDefaultLedger($user->id),
        ];
    }

    public function actionRefreshToken()
    {
        $user = Yii::$app->user->identity;
        $token = $this->userService->getToken();
        return [
            'user' => $user,
            'token' => (string)$token,
            'default_ledger' => LedgerService::getDefaultLedger($user->id),
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
     * @return User
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function actionMeUpdate(): User
    {
        $model = new UserUpdate();
        $model->id = Yii::$app->user->id;
        $this->validate($model, Yii::$app->request->bodyParams);
        return $model->save();
    }

    public function actionMe(): ?\yii\web\IdentityInterface
    {
        return Yii::$app->user->identity;
    }

    /**
     * @return string
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionSendConfirmation(): string
    {
        /** @var User $user */
        $user = User::find()->where(['id' => Yii::$app->user->id])->one();
        if ($user->status == UserStatus::ACTIVE) {
            throw new InvalidArgumentException(Yii::t('app', 'Your mailbox has been activated.'));
        }
        if ($this->mailerService->sendConfirmationMessage($user)) {
            return Yii::t('app', 'The activation email was sent successfully, please activate within 24 hours.');
        }
        return '';
    }

    /**
     * @return array
     */
    public function actionGetAuthClients(): array
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
    public function actionPasswordResetRequest(): string
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
    public function actionPasswordResetTokenVerification(): string
    {
        $model = new PasswordResetTokenVerification();
        /** @var PasswordResetRequest $model */
        $this->validate($model, Yii::$app->request->bodyParams);
        return '';
    }

    /**
     * Process user sign-up confirmation
     *
     * @return array
     * @throws InvalidArgumentException|\Throwable
     */
    public function actionConfirm(): array
    {
        $model = new JoinConfirm();
        /** @var JoinConfirm $model */
        $model = $this->validate($model, Yii::$app->request->bodyParams);
        $user = $model->confirm();
        Yii::$app->user->setIdentity($user);
        $token = $this->userService->getToken();
        return [
            'user' => $user,
            'token' => (string)$token,
            'default_ledger' => LedgerService::getDefaultLedger($user->id),
        ];
    }

    /**
     * Process password reset
     * @return bool
     * @throws InvalidArgumentException|Exception
     */
    public function actionPasswordReset(): bool
    {
        $params = Yii::$app->request->bodyParams;
        $model = new PasswordReset();
        /** @var PasswordReset $model */
        $model = $this->validate($model, $params);
        return $model->resetPassword();
    }


    /**
     * @return string
     * @throws Exception
     */
    public function actionChangePassword(): string
    {
        $params = Yii::$app->request->bodyParams;
        $model = new ChangePassword();
        /** @var ChangePassword $model */
        $model = $this->validate($model, $params);
        return $model->change();
    }

    /**
     * @return array
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionUpgradeToProRequest(): array
    {
        $record = $this->userProService->upgradeToPro();
        $price = Setup::toYuan(params('proUserPriceCent'));
        $qrCode = $this->payService->alipay($record, $price);

        return [
            'record' => $record,
            'qrCode' => $qrCode,
            'price' => $price,
        ];
    }

    /**
     * @return array
     * @throws InvalidArgumentException|UserNotProException
     */
    public function actionUpdateSettings(): array
    {
        $params = Yii::$app->request->bodyParams;
        $userId = Yii::$app->user->id;
        UserService::validateBySetting($userId, array_keys($params));
        UserService::checkAccessBySetting(array_keys($params));
        $setting = Yii::$app->userSetting;
        $userSettingKeys = UserSettingKeys::items();
        foreach ($params as $key => $value) {
            if (!in_array($key, $userSettingKeys)) {
                throw new InvalidArgumentException();
            }
            $setting->set($key, $value, $userId, '');
        }
        $data = $setting->getAllByUserId($userId);
        return $data ?: [];
    }


    public function actionGetSettings(): array
    {
        $setting = Yii::$app->userSetting;
        $userId = Yii::$app->user->id;
        $data = $setting->getAllByUserId($userId);
        return $data ?: [];
    }

    public function actionGetUserProRecord(string $out_sn)
    {
        return $this->userProService->getUserProRecord($out_sn);
    }

    public function actionGetUserPro()
    {
        $user = Yii::$app->user->identity;
        return $user->pro;
    }
}
