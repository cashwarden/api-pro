<?php

use yii\helpers\Html;

/* @var $this \yii\web\View view component instance */
/* @var $message \yii\mail\MessageInterface the message being composed */
/* @var $content string main view render result */
/* @var $tips string */
/* @var $title string */
/* @var $subject string */
/* @var $logo string */

?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?= Yii::$app->charset ?>"/>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<div bgcolor="#ffffff"
     style="width:100%;padding-bottom:0px;padding-right:0px;padding-top:0px;padding-left:0px;margin-right:0px;margin-left:0px;margin-top:0px;margin-bottom:0px;">
    <table
        style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto"
        cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
            <td style="padding-top:0;padding-right:20px;padding-bottom:0;padding-left:20px">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" dir="ltr"
                       style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;min-width:360px;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                    <tbody>
                    <tr>
                        <td bgcolor="#ffffff"></td>
                    </tr>
                    <tr>
                        <td bgcolor="#ffffff" align="center">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff"
                                   align="center"
                                   style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                <tbody>
                                <tr>
                                    <td align="center">
                                        <table cellspacing="0" cellpadding="0" border="0" width="100%"
                                               style="max-width:560px;border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                            <tbody>
                                            <tr>
                                                <td bgcolor="#4184f3">
                                                    <table cellspacing="0" cellpadding="0" border="0" width="100%"
                                                           style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto;">
                                                        <tbody>
                                                        <tr>
                                                            <td width="100%"
                                                                style="padding-top:50px;padding-right:0;padding-bottom:20px;padding-left:0">
                                                                <!--                                                                <img src="-->
                                                                <?php //= $logoLink ?><!--" width="95" border="0" style="display:block;width:95px;border-top-style:none;border-right-style:none;border-bottom-style:none;border-left-style:none" class="CToWUd">-->
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td bgcolor="#4184f3">
                                                    <table cellspacing="0" cellpadding="0" border="0" width="100%"
                                                           style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                        <tbody>
                                                        <tr>
                                                            <td width="22" bgcolor="#4184f3">&nbsp;</td>
                                                            <td bgcolor="#ffffff" height="30">&nbsp;</td>
                                                            <td width="22" bgcolor="#4184f3">&nbsp;</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                        <table cellspacing="0" cellpadding="0" border="0" width="100%"
                                               style="max-width:560px;border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                            <tbody>
                                            <tr>
                                                <td bgcolor="#fafafa"
                                                    style="padding-left:0;padding-bottom:20px;padding-right:0;padding-top:0;border-right-width:1px;border-right-style:solid;border-right-color:#f0f0f0;border-bottom-color:#f0f0f0;border-bottom-style:solid;border-bottom-width:1px;border-left-style:solid;border-left-color:#f0f0f0;border-left-width:1px">
                                                    <table width="100%" cellspacing="0" cellpadding="0" border="0"
                                                           style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                        <tbody>
                                                        <tr>
                                                            <td width="20">&nbsp;</td>
                                                            <td bgcolor="#ffffff"
                                                                style="border-top-color:white;border-right-color:#f0f0f0;border-bottom-color:#f0f0f0;border-left-color:#f0f0f0;border-top-style:solid;border-right-style:solid;border-bottom-style:solid;border-left-style:solid;border-top-width:1px;border-right-width:1px;border-bottom-width:1px;border-left-width:1px">
                                                                <table width="100%" cellspacing="0" cellpadding="0"
                                                                       border="0"
                                                                       style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                                    <tbody>
                                                                    <tr>
                                                                        <td width="100%"
                                                                            style="padding-right:30px;padding-bottom:25px;padding-left:30px">
                                                                            <table width="100%" cellspacing="0"
                                                                                   cellpadding="0" border="0"
                                                                                   style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td colspan="4"
                                                                                        style="font-family:'Roboto',Arial,Helvetica,sans-serif;font-weight:normal;font-size:14px;line-height:22px;color:#444444;padding-right:0;padding-left:0">
                                                                                        <h2><?= $this->params['title'] ?></h2>
                                                                                        <br>
                                                                                        <p><?= isset($this->params['subject']) ? $this->params['subject'] : null; ?></p>
                                                                                        <table style="width:100%">
                                                                                            <tbody>
                                                                                            <?= $content ?>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height:1px;font-size:1px;background-color:#bababa"
                                                                            height="1" bgcolor="#bababa">&nbsp;
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height:1px;font-size:1px;background-color:#c8c8c8"
                                                                            height="1" bgcolor="#c8c8c8">&nbsp;
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height:1px;font-size:1px;background-color:#dfdfdf"
                                                                            height="1" bgcolor="#dfdfdf">&nbsp;
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td style="line-height:1px;font-size:1px;background-color:#ededed"
                                                                            height="1" bgcolor="#ededed">&nbsp;
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                            <td width="20">&nbsp;</td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                                           align="center"
                                                           style="max-width:560px;border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                        <tbody>
                                                        <tr>
                                                            <td style="font-size:10px;color:#666666;padding-top:22px;padding-right:30px;padding-bottom:30px;padding-left:30px">
                                                                <table width="100%" cellpadding="0" cellspacing="0"
                                                                       border="0"
                                                                       style="border-collapse:collapse;font-family:Helvetica,Arial,sans-serif;font-weight:300;color:#444444;margin-top:0;margin-right:auto;margin-bottom:0;margin-left:auto">
                                                                    <tbody>
                                                                    <tr></tr>
                                                                    <tr>
                                                                        <td colspan="2"
                                                                            style="color:#666666;font-family:'Roboto',Arial,Helvetica,sans-serif;font-size:10px;line-height:18px;padding-bottom:10px">
                                                                            <?= isset($this->params['tips']) ? $this->params['tips'] : null; ?>
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="2"
                                                                            style="color:#666666;font-family:'Roboto',Arial,Helvetica,sans-serif;font-size:10px;line-height:18px;padding-bottom:10px">
                                                                            <?= Yii::$app->name ?>
                                                                            是一款全新资产管理系统，让您轻松、快速的记账，对自己的资产一目了然。
                                                                        </td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td colspan="2"
                                                                            style="color:#666666;font-family:'Roboto',Arial,Helvetica,sans-serif;font-size:10px;line-height:18px;padding-bottom:10px">
                                                                            <span>
                                                                                <a href="<?= params('frontendURL') ?>"
                                                                                   style="text-decoration:none;color:#666666"
                                                                                   target="_blank">
                                                                                    ©<?= date('Y') ?> <?= Yii::$app->name ?> Inc.
                                                                                </a>
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
