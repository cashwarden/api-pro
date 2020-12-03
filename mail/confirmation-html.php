<?php

/* @var $this yii\web\View */
/* @var $user \app\core\models\User */
/* @var $resetURL string */

$this->params['title'] = '欢迎加入' . Yii::$app->name;
$this->params['subject'] = '在开始使用之前，请确认你的邮箱账号，你可以在 24 小时内点击下面的「确认账号」按钮来进行确认。';
$this->params['tips'] = '如果对此电子邮件有疑问，可不必理会。';
?>

<tr>
    <td style="width:30%;"></td>
    <td style="width:40%;text-align:center">
        <a href="<?= $resetURL ?>"
           style="background-color:#4285f4;border-radius:3px;color:#ffffff;display:inline-block;font-family:Arial,Helvetica,san-serif;font-size:16px;font-weight:lighter;line-height:40px;text-align:center;text-decoration:none;text-transform:uppercase;width:100%"
           bgcolor="#4285f4" align="center" width="100%" target="_blank">
            确认账号
        </a>
    </td>
    <td></td>
</tr>
<tr style="height:20px">
    <td style="height:20px" colspan="3">
        <div style="margin: 8px 0;">如果按钮无效，请将以下链接复制到浏览器地址栏完成邮箱激活。</div>
        <div>
            <a href="<?= $resetURL ?>"
               style="color: #35c8e6; word-break: break-all" target="_blank">
                <?= $resetURL ?></a>
        </div>
    </td>
</tr>
