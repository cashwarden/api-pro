<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

use app\core\exceptions\ErrorCodes;

return [
    ErrorCodes::INVALID_ARGUMENT_ERROR => '参数异常',
    ErrorCodes::CANNOT_OPERATE_ERROR => '不能操作',
    ErrorCodes::THIRD_PARTY_SERVICE_ERROR => '第三方服务异常',
    ErrorCodes::FILE_ENCODING_ERROR => '文件编码必须为 UTF-8 格式',
    ErrorCodes::PAY_ERROR => '交易异常',
    ErrorCodes::USER_NOT_PRO_ERROR => '请升级为 Pro 会员',
];
