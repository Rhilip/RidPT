<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/9
 * Time: 9:50
 *
 * @var League\Plates\Template\Template $this
 */
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->insert('layout/head'); ?>

    <title><?= app()->config->get('base.site_name') ?> :: Authorization Point -- Powered by RidPT</title>

    <link rel="stylesheet" href="/lib/layui/src/css/layui.css"> <?php /** https://www.layui.com/doc/ */ ?>

    <!-- Custom stlylesheet -->
    <link rel="stylesheet" href="/static/css/main.css?<?= app()->config->get('base.site_css_update_date') ?>"/>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/lib/html5shiv/dist/html5shiv.min.js"></script>
    <script src="/lib/respond/dest/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="auth-container">
    <div class="layui-row auth-nav" id="auth-nav" align="center">
        <ul class="layui-nav">
            <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/auth/login', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/auth/login">Authenticate</a></li>
            <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/auth/register', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/auth/register">Recruit</a></li>
        </ul>
    </div>
    <div class="layui-row auth-body" id="auth-body">
        <div class="layui-col-xs4 layui-col-sm7 layui-col-md8">
            <div class="auth-main-panel" id="auth-main-panel">
                <div class="auth-panel">
                    <?= $this->section('panel') ?> <!-- Panel Content -->
                </div>
            </div>
        </div>
    </div>
    <footer class="auth-footer" id="auth-footer">
        <!-- The water meter is not here! -->
        <p class="auth-footer-text">
            CSS3, Javascript and Cookie support are required.<br>
            See more Browsers we support on <a href="https://browsehappy.com/" target="_blank">Browse Happy</a>.
        </p>
    </footer>
</div>

<script src="/lib/layui/src/layui.js"></script>
<script src="/static/js/main.js?<?= app()->config->get('base.site_css_update_date') ?>"></script>
</body><!-- This templates was made by Colorlib (https://colorlib.com) -->
</html>
