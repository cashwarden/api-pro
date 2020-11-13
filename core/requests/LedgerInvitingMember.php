<?php

namespace app\core\requests;

use app\core\exceptions\InvalidArgumentException;
use app\core\models\Ledger;
use app\core\models\User;
use Yii;
use yii\base\Model;

class LedgerInvitingMember extends Model
{
    public $email;
    public $ledger_id;
    public $rule;

    public $user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            [['email', 'ledger_id', 'rule'], 'required'],
            ['email', 'email'],
            [
                'ledger_id',
                'exist',
                'targetClass' => Ledger::class,
                'targetAttribute' => 'id',
                'filter' => ['user_id' => Yii::$app->user->id],
            ],
        ];
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validateGroup()
    {
        if (!$user = User::find()->where(['email' => $this->email])->asArray()->one()) {
            throw new InvalidArgumentException(\Yii::t('app', 'This user does not exist.'));
        }
        $this->user_id = $user['id'];
    }
}
