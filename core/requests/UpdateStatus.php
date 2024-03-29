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

use app\core\exceptions\InternalException;

class UpdateStatus extends \yii\base\Model
{
    /**
     * @var string
     */
    public $status;

    /**
     * @var array
     */
    private $statusRange;

    /**
     * UpdateStatus constructor.
     * @param array $statusRange
     * @param array $config
     * @throws InternalException
     */
    public function __construct(array $statusRange, array $config = [])
    {
        if (!count($statusRange)) {
            throw new InternalException(\Yii::t('app', 'Status range cannot be blank.'));
        }
        $this->statusRange = $statusRange;
        parent::__construct($config);
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'required'],
            ['status', 'in', 'range' => $this->statusRange],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'status' => t('app', 'Status'),
        ];
    }
}
