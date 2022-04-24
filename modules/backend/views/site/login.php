<?php

/* @var $this yii\web\View */
/* @var $form ActiveForm */

/* @var $model LoginForm */

use app\modules\backend\models\LoginForm;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = '登录';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login my-4">
    <div class="card">
        <div class="card-header">
            <strong>
                <?= $this->title ?>
            </strong>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'horizontalCssClasses' => [
                        'label' => 'col-sm-4',
                        'offset' => 'offset-sm-4',
                        'wrapper' => 'col-sm-3',
                        'error' => '',
                        'hint' => '',
                    ],
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'rememberMe')->checkbox([
                'template' => "<span class='ml-4'></span>
<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
            ]) ?>

            <div class="form-group">
                <div class="col-lg-offset-1 col-lg-11">
                    <?= Html::submitButton('登录', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>
