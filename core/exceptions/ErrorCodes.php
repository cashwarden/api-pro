<?php

namespace app\core\exceptions;

class ErrorCodes
{
    public const INTERNAL_ERROR = 500;
    public const INVALID_ARGUMENT_ERROR = 901;
    public const CANNOT_OPERATE_ERROR = 902;
    public const THIRD_PARTY_SERVICE_ERROR = 903;
    public const NOT_USER_ERROR = 904;

    public const FILE_ERROR = 910;
    public const FILE_ENCODING_ERROR = 911;

    public const PAY_ERROR = 920;

    public const USER_NOT_PRO_ERROR = 930;
}
