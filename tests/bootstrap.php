<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

define('TEST_ROOT', __DIR__);
define('STUBS_ROOT', __DIR__ . '/fixtures');

$_SERVER['HTTP_HOST'] = 'localhost:8080';

include __DIR__ . '/../vendor/autoload.php';
