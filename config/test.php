<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

use yii\helpers\ArrayHelper;

$common = require __DIR__ . '/common.php';
$web = require __DIR__ . '/web.php';
$params = require __DIR__ . '/params.php';


$config = ArrayHelper::merge([
    'id' => 'basic-tests',
], $web);

return ArrayHelper::merge($common, $config);
