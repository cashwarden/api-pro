<?php

use app\core\models\User;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="user-index my-4">

        <h1 class="pb-1"><?= Html::encode($this->title) ?></h1>

        <?php $form = ActiveForm::begin([
            'method' => 'get',
            'action' => 'index',
            'options' => ['class' => ['row g-3']]

        ]); ?>

        <div class="col-auto">
            <?= Html::textInput(
                'username',
                request('username'),
                ['class' => 'form-control mb-2 mr-sm-2', 'placeholder' => '用户名']
            ) ?>
        </div>

        <div class="col-auto">
            <?= Html::textInput(
                'email',
                request('email'),
                ['class' => 'form-control mb-2 mr-sm-2', 'placeholder' => '邮箱']
            ) ?>
        </div>

        <div class="col-auto">
            <?= Html::textInput(
                'id',
                request('id'),
                ['class' => 'form-control mb-2 mr-sm-2', 'placeholder' => '用户ID']
            ) ?>
        </div>

        <div class="col-auto">
            <?= Html::submitButton('搜索', ['class' => 'btn btn-primary mb-2']) ?>
            <?= Html::resetButton('重置', ['class' => 'btn btn-secondary mb-2']) ?>
        </div>

        <?php ActiveForm::end(); ?>


        <?php Pjax::begin([
            'scrollTo' => 0,
            'formSelector' => false,
            'linkSelector' => '.pagination a'
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n{summary}\n<div class='pt-2'>{pager}</div>",
            'columns' => [
                'id',
                'username',
                'email',
                [
                    'attribute' => 'status',
                    'value' => function (User $model) {
                        return \app\core\types\UserStatus::getName($model->status);
                    },
                ],
                'created_at:datetime',
                [
                    'label' => '会员到期时间',
                    'value' => function (User $model) {
                        return $model->pro ? $model->pro->ended_at : '';
                    },
                ],
                [
                    'label' => '操作',
                    'format' => 'raw',
                    'value' => function (User $model) {
                        $html = '';
                        $html .= Html::a(
                            '🎁',
                            '#',
                            [
                                'title' => '送会员',
                                'class' => 'upgrade-pro',
                                'data' => ['bs-toggle' => 'modal', 'bs-target' => '#upgrade-modal', 'id' => $model->id]
                            ]
                        );
                        return $html;
                    },
                ],
            ],
        ]); ?>


        <?php Pjax::end(); ?>
    </div>
<?php
Modal::begin([
    'id' => 'upgrade-modal',
    'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>',
]);
$requestUpdateUrl = Url::toRoute('upgrade-pro');
$updateJs = <<<JS
    $('.upgrade-pro').on('click', function () {
        $.get('{$requestUpdateUrl}', {id: $(this).closest('tr').data('key')},
            function (data) {
                $('.modal-body').html(data);
            }
        );
    });
    $('#upgrade-modal').on('hidden.bs.modal', function (e) {
        location.reload();
    })
JS;
$this->registerJs($updateJs);
Modal::end();
