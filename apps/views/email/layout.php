<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/5
 * Time: 15:29
 *
 * @var League\Plates\Template\Template $this
 */

$schme = app()->request->isSecure() ? 'https://' : 'http://';
$site_url = $schme . app()->config->get('base.site_url');
$icon_img = $site_url . '/static/pic/logo.png';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
    <meta content="width=device-width" name="viewport"/>
    <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
    <style type="text/css">
        body{margin:0;padding:0;background-color:#f1f3f3;font-family:'Helvetica Neue',Helvetica,Arial,sans-serif;-webkit-text-size-adjust:100%}
        table,td,tr{border-collapse:collapse;vertical-align:top}
        *{line-height:inherit}
        a[x-apple-data-detectors=true]{color:inherit!important;text-decoration:none!important}
        [owa] .img-container button,[owa] .img-container div{display:block!important}
        [owa] .fullwidth button{width:100%!important}
        [owa] .block-grid .col{float:none!important;display:table-cell;vertical-align:top}
        .block-grid{width:100%!important;max-width:650px;min-width:320px;background-color:transparent;word-wrap:break-word;Margin:0 auto;overflow-wrap:break-word;word-break:break-word}
        .block-grid .col{display:block;vertical-align:top}
        .block-grid .col.num12{width:650px!important}
        .col{width:100%!important}
        .col>div{margin:0 auto}
        .no-stack .col{display:table-cell!important;min-width:0!important}
        .no-stack.two-up .col{width:50%!important}
        .desktop_hide{display:block!important;max-height:none!important}
        .nl-container{width:100%;min-width:320px;border-collapse:collapse;background-color:#f1f3f3;table-layout:fixed;vertical-align:top;Margin:0 auto;border-spacing:0}
        div.block-grid-item{display:table-cell;width:100%;max-width:650px;min-width:320px;border-collapse:collapse;background-color:transparent;vertical-align:top}
        div.block-grid-item>div{width:100%!important;background-color:#fff}
        div.item-1{font-size:12px;font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;line-height:14px}
        div.item-0{border-top:0 solid transparent;border-right:8px solid #f1f3f3;border-bottom:0 solid transparent;border-left:8px solid #f1f3f3}
        div.pandding{width:100%!important;height:35px}
        img.icon{float:none;clear:both;display:block;width:100%;height:auto;max-width:158px;outline:0;border:0;text-decoration:none;-ms-interpolation-mode:bicubic}
    </style>
</head>
<body>
<table bgcolor="#F1F3F3" cellpadding="0" cellspacing="0" class="nl-container" role="presentation" valign="top" width="100%">
    <tbody>
    <tr style="vertical-align: top;" valign="top">
        <td style="word-break: break-word; vertical-align: top; border-collapse: collapse;" valign="top">
            <div class="block-grid">
                <div class="block-grid-item col num12 pandding"></div>
                <div class="block-grid-item col num12">
                    <div style="background-color:#FFFFFF">
                        <div class="item-0" style="padding: 50px 50px 5px;">
                            <div class="desktop_hide" style="max-height: 0; overflow: hidden;">
                                <div style="color:#66BECD;line-height:120%;padding: 10px;">
                                    <div class="item-1" style="color: #66BECD;">
                                        <p style="line-height: 28px; text-align: left; margin: 0;font-size: 24px;"><?= $this->section('subject') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="item-1" style="color:#CFCFCF;line-height:120%;padding: 10px;">
                                <p style="font-size: 14px; line-height: 16px; text-align: left; margin: 0;">
                                    <strong>Dear <?= $this->section('username', 'User') ?>: </strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-grid-item col num12">
                    <div style="background-color:#FFFFFF">
                        <div class="item-0" style="padding: 0 50px 35px;">
                            <div style="color:#555555;line-height:150%;padding: 15px 10px 10px;font-size: 14px; text-align: left; margin: 0;">
                                <?= $this->section('body') ?>
                                <hr>
                                Time: <?= date('Y-m-d H:i:s'); ?>
                                IP: <?= app()->request->getClientIp(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-grid-item col num12">
                    <div style="background-color:#E3FAFF">
                        <div class="item-0" style="padding: 30px 0 25px;">
                            <div align="center" class="img-container center fixedwidth">
                                <img align="center" alt="Image" border="0" class="center fixedwidth icon" src="<?= $icon_img ?>" title="Image" width="158"/>
                            </div>
                            <div class="item-1" style="color:#353535;line-height:120%;padding: 10px 10px 0;">
                                <p style="font-size: 14px; line-height: 16px; text-align: center; margin: 0;">
                                    <strong><?= app()->config->get('base.site_name') ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-grid-item col num12">
                    <div>
                        <div class="item-0" style="padding: 30px 50px;">
                            <div class="item-1" style="color:#555555;line-height:120%;padding: 10px;">
                                <p style="font-size: 14px; line-height: 16px; text-align: left; margin: 0;">From : <a href="<?= $site_url ?>" target="_blank"><?= $site_url ?></a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="block-grid-item col num12 pandding"></div>
            </div>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
