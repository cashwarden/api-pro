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

class TelegramAction
{
    public const TRANSACTION_RATING = 'tr'; // transaction_rating
    public const RECORD_DELETE = 'nrd'; // new_record_delete
    public const TRANSACTION_DELETE = 'td'; // transaction_delete
    public const FIND_CATEGORY_RECORDS = 'fcr'; // find_category_records
}
