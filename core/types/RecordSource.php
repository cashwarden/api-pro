<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace app\core\types;

class RecordSource extends BaseType
{
    public const WEB = 1;
    public const TELEGRAM = 2;
    public const CRONTAB = 3;
    public const IMPORT = 4;

    public static function names(): array
    {
        return [
            self::WEB => 'web',
            self::TELEGRAM => 'telegram',
            self::CRONTAB => 'crontab',
            self::IMPORT => 'import',
        ];
    }
}
