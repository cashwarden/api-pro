<?php

/**
 * author     : forecho <caizhenghai@gmail.com>
 * createTime : 2020/2/20 1:50 下午
 * description:
 */

use app\core\models\User;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $form ActiveForm */
/* @var $model \app\modules\backend\models\UpgradeProForm */
/** @var User $user */

$this->title = '升级用户';
?>
    <div class="alert alert-success d-none" id="modal_form-alert" role="alert">
        操作成功
    </div>

    <p class="text-muted txt-info">会员到期时间，会按最长时间计算，请确认好时间再操作</p>

    <div class="upgrade-pro-form">
        <?php $form = ActiveForm::begin([
            'id' => 'modal_form',
        ]); ?>

        <?= $form->field($model, 'username')->textInput(['disabled' => true]) ?>

        <?= $form->field($model, 'date')->input('date') ?>

        <div class="form-group">
            <div class="col-lg-offset-1 col-lg-11">
                <?= Html::submitButton('提交', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
<?php
$js = <<<JS
$('#modal_form').on('submit', function(e){
    e.preventDefault();
     var form = $(this);
    var formData = form.serialize();
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formData,
        success: function (data) {
            $('#modal_form-alert').addClass('d-block').removeClass('d-none');
        }
    });
    return false;
});
JS;
$this->registerJs($js);
