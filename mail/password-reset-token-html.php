<?php

use app\core\models\User;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user User */
/** @var $resetURL string */

$this->params['title'] = '您好 ' . Html::encode($user->username) . '，';
$this->params['subject'] = '你可以在 24 小时内点击下面的「重置密码」按钮来重置密码。';
$this->params['tips'] = '如果这不是您本人发起的动作，请忽略此操作。';
?>
<tr>
    <td style="width:30%;"></td>
    <td style="width:40%;text-align:center">
        <a href="<?= $resetURL ?>"
           style="background-color:#4285f4;border-radius:3px;color:#ffffff;display:inline-block;font-family:Arial,Helvetica,san-serif;font-size:16px;font-weight:lighter;line-height:40px;text-align:center;text-decoration:none;text-transform:uppercase;width:100%"
           bgcolor="#4285f4" align="center" width="100%" target="_blank">
            重置密码
        </a>
    </td>
    <td></td>
</tr>
<tr style="height:20px">
    <td style="height:20px" colspan="3">
        <div style="margin: 8px 0;">如果按钮无效，请将下面的地址复制到浏览器打开，完成操作。</div>
        <div>
            <a href="<?= $resetURL ?>"
               style="color: #35c8e6; word-break: break-all" target="_blank">
                <?= $resetURL ?></a>
        </div>
    </td>
</tr>
