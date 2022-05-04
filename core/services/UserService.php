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

use app\core\exceptions\InternalException;
use app\core\exceptions\InvalidArgumentException;
use app\core\exceptions\UserNotProException;
use app\core\models\Account;
use app\core\models\AuthClient;
use app\core\models\Category;
use app\core\models\Ledger;
use app\core\models\User;
use app\core\requests\JoinRequest;
use app\core\requests\MemberFormRequest;
use app\core\requests\PasswordResetRequest;
use app\core\traits\ServiceTrait;
use app\core\types\AccountType;
use app\core\types\AuthClientStatus;
use app\core\types\AuthClientType;
use app\core\types\ColorType;
use app\core\types\LedgerType;
use app\core\types\TransactionType;
use app\core\types\UserRole;
use app\core\types\UserSettingKeys;
use app\core\types\UserStatus;
use bizley\jwt\Jwt;
use Carbon\Carbon;
use DateTimeImmutable;
use Exception;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception as DBException;
use yii\helpers\ArrayHelper;
use yiier\helpers\ModelHelper;
use yiier\helpers\Setup;

class UserService
{
    use ServiceTrait;

    /**
     * @param  JoinRequest  $request
     * @return User
     * @throws InternalException|\Throwable
     */
    public function createUser(JoinRequest $request): User
    {
        $transaction = Yii::$app->db->beginTransaction();
        $user = new User();
        try {
            $user->username = $request->username;
            $user->email = $request->email;
            $user->base_currency_code = $request->base_currency_code;
            $user->setPassword($request->password);
            $user->generateAuthKey();
            $user->status = params('verificationEmail') ? UserStatus::UNACTIVATED : UserStatus::ACTIVE;
            if (!$user->save()) {
                throw new DBException(Setup::errorMessage($user->firstErrors));
            }
            $this->createUserAfterInitData($user);

            $endedAt = Carbon::parse('2020-12-31')->endOfDay();
            UserProService::upgradeToProBySystem($user->id, $endedAt);

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();
            Yii::error(
                ['request_id' => Yii::$app->requestId->id, $user->attributes, $user->errors, (string) $e],
                __FUNCTION__
            );
            throw new InternalException($e->getMessage());
        }
        return $user;
    }


    /**
     * @return string
     * @throws \Throwable
     */
    public function getToken(): string
    {
        /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        if (!$jwt->signingKey) {
            throw new InternalException(t('app', 'The JWT secret must be configured first.'));
        }
        $now = new DateTimeImmutable();
        return $jwt->getBuilder()
            ->issuedBy(params('appURL'))
            ->identifiedBy(Yii::$app->name)
            ->issuedAt($now)
            ->expiresAt($now->modify('+7 day'))
            ->withClaim('username', \user('username'))
            ->withClaim('id', \user('id'))
            ->getToken($jwt->getConfiguration()->signer(), $jwt->getConfiguration()->signingKey())
            ->toString();
    }


    /**
     * @param  string  $value
     * @return User|ActiveRecord|null
     */
    public static function getUserByUsernameOrEmail(string $value)
    {
        $condition = strpos($value, '@') ? ['email' => $value] : ['username' => $value];
        return User::find()->andWhere($condition)->one();
    }

    /**
     * @param  User  $user
     * @return bool
     * @throws \yii\base\Exception
     */
    public function setPasswordResetToken(User $user): bool
    {
        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
        }

        if (!$user->save()) {
            return false;
        }
        return true;
    }

    /**
     * @param  User  $user
     * @throws DBException
     * @throws \app\core\exceptions\InvalidArgumentException
     */
    public function createUserAfterInitData(User $user): void
    {
        try {
            $account = new Account();
            $account->setAttributes([
                'name' => Yii::t('app', 'General Account'),
                'type' => AccountType::getName(AccountType::GENERAL_ACCOUNT),
                'user_id' => $user->id,
                'currency_balance' => 0,
                'default' => (bool) Account::DEFAULT,
                'currency_code' => $user->base_currency_code,
            ]);
            if (!$account->save()) {
                throw new DBException('Init Account fail ' . Setup::errorMessage($account->firstErrors));
            }
            $ledger = new Ledger();
            $ledger->name = '日常生活';
            $ledger->type = LedgerType::getName(LedgerType::GENERAL);
            $ledger->user_id = $user->id;
            $ledger->default = true;
            if (!$ledger->save()) {
                throw new \Exception(Setup::errorMessage($ledger->firstErrors));
            }
            $items = [
                [
                    'name' => Yii::t('app', 'Food and drink'),
                    'color' => ColorType::RED,
                    'icon_name' => 'food',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Home life'),
                    'color' => ColorType::ORANGE,
                    'icon_name' => 'home',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Traffic'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'bus',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Recreation'),
                    'color' => ColorType::VOLCANO,
                    'icon_name' => 'game',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Health care'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'medicine-chest',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Clothes'),
                    'color' => ColorType::PURPLE,
                    'icon_name' => 'clothes',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Cultural education'),
                    'color' => ColorType::CYAN,
                    'icon_name' => 'education',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Investment expenditure'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'investment',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Childcare'),
                    'color' => ColorType::LIME,
                    'icon_name' => 'baby',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Other expenses'),
                    'color' => ColorType::GEEK_BLUE,
                    'icon_name' => 'expenses',
                    'transaction_type' => TransactionType::EXPENSE,
                    'default' => Account::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Work income'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'work',
                    'transaction_type' => TransactionType::INCOME,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Investment income'),
                    'color' => ColorType::GOLD,
                    'icon_name' => 'investment',
                    'transaction_type' => TransactionType::INCOME,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Other income'),
                    'color' => ColorType::MAGENTA,
                    'icon_name' => 'income',
                    'transaction_type' => TransactionType::INCOME,
                    'default' => Category::DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Transfer'),
                    'color' => ColorType::GREEN,
                    'icon_name' => 'transfer',
                    'transaction_type' => TransactionType::TRANSFER,
                    'default' => Category::NOT_DEFAULT,
                ],
                [
                    'name' => Yii::t('app', 'Adjust Balance'),
                    'color' => ColorType::BLUE,
                    'icon_name' => 'adjust',
                    'transaction_type' => TransactionType::ADJUST,
                    'default' => Category::NOT_DEFAULT,
                ],
            ];
            $time = date('Y-m-d H:i:s');
            $rows = [];
            foreach ($items as $key => $value) {
                $rows[$key] = $value;
                $rows[$key]['user_id'] = $user->id;
                $rows[$key]['created_at'] = $time;
                $rows[$key]['updated_at'] = $time;
                $rows[$key]['ledger_id'] = $ledger->id;
            }
            if (!ModelHelper::saveAll(Category::tableName(), $rows)) {
                throw new DBException('Init Category fail');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getAuthClients(): array
    {
        $data = [];
        if ($items = AuthClient::find()->where(['user_id' => Yii::$app->user->id])->all()) {
            $items = ArrayHelper::index($items, 'type');
            foreach (AuthClientType::names() as $key => $value) {
                $data[$value] = data_get($items, $key, '');
            }
        }

        return $data;
    }

    /**
     * @param  string  $token
     * @return User|array|ActiveRecord|null
     * @throws InvalidArgumentException
     */
    public function getUserByResetToken(string $token)
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Token 验证失败，请重新操作。');
        }

        if (!$user = User::findByPasswordResetToken($token)) {
            throw new InvalidArgumentException(
                Yii::t('app', 'The link is invalid or has expired, please try again.')
            );
        }
        return $user;
    }

    /**
     * @param  int  $type
     * @param  string  $clientId
     * @return User
     * @throws Exception
     */
    public function getUserByClientId(int $type, string $clientId): User
    {
        /** @var AuthClient $model */
        if ($model = AuthClient::find()->where(['type' => $type, 'client_id' => $clientId])->one()) {
            return $model->user;
        }
        throw new Exception('您还未绑定账号，请先访问「个人设置」中的「账号绑定」进行绑定账号，然后才能快速记账。');
    }

    /**
     * @param  PasswordResetRequest  $request
     * @return bool whether the email was send
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function sendPasswordResetEmail(PasswordResetRequest $request): bool
    {
        /* @var $user User */
        $user = User::findOne(['status' => [UserStatus::ACTIVE, UserStatus::UNACTIVATED], 'email' => $request->email]);
        $this->setPasswordResetToken($user);
        return $this->getMailerService()->sendPasswordResetMessage($user);
    }


    /**
     * @param  array  $params
     * @return bool
     * @throws UserNotProException
     */
    public static function checkAccessBySetting(array $params): bool
    {
        if (UserProService::isPro()) {
            return true;
        }
        $proItems = [
            UserSettingKeys::MONTHLY_REPORT,
            UserSettingKeys::WEEKLY_REPORT,
            UserSettingKeys::DAILY_REPORT,
        ];
        if (array_intersect($params, $proItems)) {
            throw new UserNotProException();
        }
        return true;
    }

    /**
     * @param  int  $userId
     * @param  array  $params
     * @throws InvalidArgumentException
     */
    public static function validateBySetting(int $userId, array $params): void
    {
        $items = [
            UserSettingKeys::MONTHLY_REPORT,
            UserSettingKeys::WEEKLY_REPORT,
            UserSettingKeys::DAILY_REPORT,
        ];
        if (array_intersect($params, $items)) {
            if (!AuthClient::find()->where(['type' => AuthClientType::TELEGRAM, 'user_id' => $userId])->exists()) {
                throw new InvalidArgumentException('请先绑定 Telegram');
            }
        }
    }

    /**
     * @param  int  $userId
     * @param  int  $type
     * @param  array  $expand
     * @return AuthClient|array|ActiveRecord
     * @throws DBException
     */
    public static function findOrCreateAuthClient(int $userId, int $type, array $expand = [])
    {
        $conditions = [
            'type' => $type,
            'user_id' => $userId,
            'status' => AuthClientStatus::ACTIVE,
        ];
        if (!$model = AuthClient::find()->where($conditions)->one()) {
            $model = new AuthClient();
            $model->load($conditions, '');
        }
        $model->load($expand, '');
        if (!$model->save()) {
            throw new DBException(Setup::errorMessage($model->firstErrors));
        }
        return $model;
    }

    /**
     * @param  string  $type
     * @return array
     * @throws InternalException
     * @throws InvalidArgumentException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteAuthClient(string $type): array
    {
        if (!in_array($type, AuthClientType::names())) {
            throw new InvalidArgumentException('参数错误');
        }
        $type = AuthClientType::toEnumValue($type);
        $client = AuthClient::find()->where(['user_id' => Yii::$app->user->id, 'type' => $type])->one();
        if ($client && $client->delete()) {
            return $this->getAuthClients();
        }
        throw new InternalException('删除失败');
    }

    public static function getCurrentMemberIds(): array
    {
        if (params('memberIds')) {
            return params('memberIds');
        }
        /** @var User $user */
        $user = Yii::$app->user->identity;
        $parentId = $user->parent_id ?: $user->id;
        $userIds = User::find()->select('id')->where(['parent_id' => $parentId])->column();
        $userIds = array_merge($userIds, [$parentId]);
        $userIds = array_map('intval', array_unique($userIds));
        Yii::$app->params['memberIds'] = $userIds;
        return $userIds;
    }

    /**
     * @throws \yii\base\Exception
     * @throws DBException
     * @throws InvalidArgumentException
     */
    public function createUpdateMember(MemberFormRequest $data, User $user, User $parent): User
    {
        $user->load($data->attributes, '');
        $user->setPassword($data->password);
        $user->generateAuthKey();
        $user->status = $user->status ?: UserStatus::UNACTIVATED;
        $user->role = UserRole::toEnumValue($data->role);
        $user->parent_id = $parent->id;
        $user->base_currency_code = $parent->base_currency_code;
        if (!$user->save()) {
            throw new DBException(Setup::errorMessage($user->firstErrors));
        }
        return $user;
    }
}
