<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\services;

use app\core\models\User;
use app\core\traits\ServiceTrait;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class MailerService
{
    use ServiceTrait;

    /**
     * @var string|array Default: `Yii::$app->params['adminEmail']` OR `no-reply@xxx.com
     */
    public $sender;

    /**
     * @var string 欢迎用户
     */
    protected $welcomeSubject;

    /**
     * @var string 验证用户
     */
    protected $confirmationSubject;

    /**
     * @var string 再次验证用户
     */
    protected $passwordResetSubject;

    /**
     * @return string
     */
    public function getWelcomeSubject()
    {
        if ($this->welcomeSubject == null) {
            $this->setWelcomeSubject(sprintf('欢迎使用%s，请验证您的邮箱', Yii::$app->name));
        }
        return $this->welcomeSubject;
    }

    /**
     * @param string $welcomeSubject
     */
    public function setWelcomeSubject(string $welcomeSubject)
    {
        $this->welcomeSubject = $welcomeSubject;
    }

    /**
     * @return string
     */
    public function getConfirmationSubject()
    {
        if ($this->confirmationSubject == null) {
            $this->setConfirmationSubject(sprintf('欢迎使用 %s，请验证您的邮箱', Yii::$app->name));
        }
        return $this->confirmationSubject;
    }

    /**
     * @param string $confirmationSubject
     */
    public function setConfirmationSubject(string $confirmationSubject)
    {
        $this->confirmationSubject = $confirmationSubject;
    }

    /**
     * @return string
     */
    public function getPasswordResetSubject()
    {
        if ($this->passwordResetSubject == null) {
            $this->setPasswordResetSubject(sprintf('重置 %s 的用户密码', Yii::$app->name));
        }
        return $this->passwordResetSubject;
    }

    /**
     * @param string $passwordResetSubject
     */
    public function setPasswordResetSubject(string $passwordResetSubject)
    {
        $this->passwordResetSubject = $passwordResetSubject;
    }

    /**
     * Sends an email to a user after registration.
     *
     * @param User $user
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function sendWelcomeMessage(User $user)
    {
        return $this->sendMessage($user->email, $this->getWelcomeSubject(), 'welcome', ['user' => $user]);
    }

    /**
     * 验证邮箱邮件.
     *
     * @param User $user
     * @return bool
     * @throws InvalidConfigException|Exception
     */
    public function sendConfirmationMessage(User $user)
    {
        $this->getUserService()->setPasswordResetToken($user);
        $resetURL = params('frontendURL') . '#/passport/confirm-email?token=' . $user->password_reset_token;
        $params = ['user' => $user, 'resetURL' => $resetURL];
        return $this->sendMessage($user->email, $this->getConfirmationSubject(), 'confirmation', $params);
    }

    /**
     * 忘记密码邮件.
     *
     * @param User $user
     * @return bool
     * @throws InvalidConfigException
     */
    public function sendPasswordResetMessage(User $user)
    {
        $email = $user->email;
        $resetURL = params('frontendURL') . '#/passport/password-reset?token=' . $user->password_reset_token;
        $params = ['user' => $user, 'resetURL' => $resetURL];
        return $this->sendMessage($email, $this->getPasswordResetSubject(), 'password-reset-token', $params);
    }


    /**
     * @param string $to
     * @param string $subject
     * @param null $view
     * @param array $params
     * @return bool
     * @throws InvalidConfigException
     */
    protected function sendMessage(string $to, string $subject, $view = null, $params = [])
    {
        $this->initMailer();
        /** @var \yii\mail\BaseMailer $mailer */
        $mailer = Yii::$app->mailer;
        $mailer->viewPath = '@app/mail';
        $mailer->getView()->theme = Yii::$app->view->theme;
        if ($this->sender === null) {
            $this->sender = params('senderEmail') ?: '';
        }

        $message = $mailer->compose(['html' => $view . '-html'], $params)
            ->setTo($to)
            ->setFrom([$this->sender => params('senderName') ?: Yii::$app->name . '机器人'])
            ->setSubject($subject);

        return $message->send();
    }

    /**
     * @throws InvalidConfigException
     */
    private function initMailer()
    {
        // 国内线路
        Yii::$app->set('mailer', [
            'class' => 'yii\swiftmailer\Mailer',
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => Yii::$app->params['emailHost'],
                'username' => Yii::$app->params['emailUsername'],
                'password' => Yii::$app->params['emailPassword'],
                'port' => Yii::$app->params['emailPort'],
                'encryption' => Yii::$app->params['emailEncryption'],
            ],
        ]);
    }
}
