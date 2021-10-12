<?php

namespace app\modules\backend\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
    ];
    public $js = [
        'javascript/main.js',
        // 'javascript/lazysizes.min.js',
    ];
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}
