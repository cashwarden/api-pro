<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\models;

use app\core\types\UserProRecordStatus;
use app\core\types\UserRole;
use app\core\types\UserStatus;
use Carbon\Carbon;
use Lcobucci\JWT\Token\Plain;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $username
 * @property string|null $avatar
 * @property string $email
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property int|null $status
 * @property int|null $role
 * @property string $base_currency_code
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property-write string $password
 * @property-read null|\app\core\models\UserProRecord $pro
 * @property array $memberIds
 * @property-read string $authKey
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    public static function currUserIsSuperAdmin(): bool
    {
        return Yii::$app->user->id == params('superAdminUserId');
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'value' => Yii::$app->formatter->asDatetime('now'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => UserStatus::ACTIVE],
            ['email', 'string', 'max' => 255],
            ['status', 'in', 'range' => [UserStatus::ACTIVE, UserStatus::UNACTIVATED]],
            [['username'], 'string', 'max' => 60],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->where(['id' => $id, 'status' => [UserStatus::ACTIVE, UserStatus::UNACTIVATED]])
            ->limit(1)
            ->one();
    }


    /**
     * @param  string  $token
     * @param  null  $type
     * @return void|IdentityInterface
     * @throws \yii\base\InvalidConfigException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /** @var Plain $jwt */
        $jwt = Yii::$app->jwt->parse($token);
        $userId = $jwt->claims()->get('id');
        return self::findIdentity($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password.
     *
     * @param  string  $password  password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param  string  $password
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key.
     * @throws \yii\base\Exception
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token.
     * @throws \yii\base\Exception
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token.
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Finds user by password reset token.
     *
     * @param  string  $token  password reset token
     * @return User|array|ActiveRecord|null
     */
    public static function findByPasswordResetToken(string $token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::find()
            ->where(['password_reset_token' => $token, 'status' => [UserStatus::ACTIVE, UserStatus::UNACTIVATED]])
            ->one();
    }

    /**
     * Finds out if password reset token is valid.
     *
     * @param  string|null  $token  password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid(?string $token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['userPasswordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    public function extraFields()
    {
        return ['pro'];
    }

    public function getPro(): ?UserProRecord
    {
        return UserProRecord::find()
            ->where(['user_id' => $this->id, 'status' => UserProRecordStatus::PAID])
            ->andWhere(['<=', 'created_at', Carbon::now()->toDateTimeString()])
            ->orderBy(['ended_at' => SORT_DESC])
            ->one();
    }

    public function getMemberIds(): array
    {
        $userId = Yii::$app->user->id;
        $userIds = self::find()->select('id')->where(['parent_id' => $userId])->column();
        $userIds = array_merge($userIds, [$userId]);
        return array_unique($userIds);
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset(
            $fields['auth_key'],
            $fields['password_hash'],
            $fields['password_reset_token'],
            $fields['parent_id'],
            $fields['updated_at'],
        );

        $fields['status'] = function (self $model) {
            return UserStatus::getName($model->status);
        };
        $fields['role'] = function (self $model) {
            return UserRole::names()[$model->role];
        };

        $fields['role_name'] = function (self $model) {
            return UserRole::texts()[$model->role];
        };

        $fields['avatar'] = function (self $model) {
            $avatar = md5(strtolower(trim($model->avatar)));
            return "https://www.gravatar.com/avatar/{$avatar}?s=48";
        };
        return $fields;
    }
}
