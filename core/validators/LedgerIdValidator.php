<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\validators;

use app\core\models\Ledger;
use app\core\services\UserService;

class LedgerIdValidator extends \yii\validators\Validator
{
    public function init()
    {
        parent::init();
        $this->message = 'The ledger id is invalid.';
    }

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (!Ledger::find()->where(['id' => $value, 'user_id' => UserService::getCurrentMemberIds()])->exists()) {
            $this->addError($model, $attribute, $this->message);
        }
    }
}
