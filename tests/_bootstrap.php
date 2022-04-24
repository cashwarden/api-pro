<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);
file_exists(__DIR__ . '/../.env.testing') ? define('DOTENV_FILE', '.env.testing') : null;

require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../vendor/autoload.php';

new yii\web\Application(require __DIR__ . '/../config/test.php');
