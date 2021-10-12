<?php

/* @var $this \yii\web\View */

/* @var $content string */

use app\modules\backend\assets\AppAsset;
use app\modules\backend\widgets\Alert;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
    <link rel="shortcut icon" href="/favicon.png" type="image/x-png"/>
    <title><?= Yii::$app->name ?> - 后台管理系统</title>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name . ' 后台',
        'brandUrl' => ['/backend/site/index'],
    ]);

    echo Nav::widget([
        'items' => [
            ['label' => '用户', 'url' => ['/backend/user/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => '系统', 'url' => ['/backend/system/index'], 'visible' => !Yii::$app->user->isGuest]
        ],
        'options' => ['class' => 'navbar-nav me-auto mb-2 mb-lg-0'],
    ]);

    echo Nav::widget([
        'encodeLabels' => false,
        'options' => ['class' => 'nav justify-content-end'],
        'items' => [
            ['label' => '返回前台', 'url' => params('frontendURL')],
            [
                'label' => '退出',
                'url' => ['/backend/site/logout'],
                'linkOptions' => ['data-method' => 'post'],
                'visible' => !Yii::$app->user->isGuest
            ]
        ],
    ]);
    NavBar::end();
    ?>
    <div class="container">
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; <?= Yii::$app->name . ' ' . date('Y') ?></p>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
