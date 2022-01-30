<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\exceptions;

use Yii;
use yii\web\HttpException;

class PayException extends HttpException
{
    /**
     * Constructor.
     * @param string $message error message
     * @param int $code error code
     * @param \Exception|null $previous The previous exception used for the exception chaining.
     */
    public function __construct(
        $message = '',
        $code = ErrorCodes::PAY_ERROR,
        \Exception $previous = null
    ) {
        $message = $message ?: Yii::t('app/error', ErrorCodes::PAY_ERROR);
        parent::__construct(200, $message, $code, $previous);
    }
}
