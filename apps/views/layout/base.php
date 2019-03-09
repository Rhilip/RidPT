<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 11:38
 *
 * @var League\Plates\Template\Template $this
 * @var string $title
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->insert('layout/head'); ?>

    <title><?= app()->config->get('base.site_name') ?> :: <?= $this->e($this->section('title') ?? '') ?> -- Powered by <?= app()->config->get('base.site_generator') ?></title>

    <link rel="stylesheet" href="/lib/layui/src/css/layui.css"> <?php /** https://www.layui.com/doc/ */ ?>
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.css"> <?php /** https://fontawesome.com/icons?d=gallery */ ?>

    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="/static/css/main.css?<?= app()->config->get('base.site_css_update_date') ?>">

    <?= $this->section('css') ?> <!-- Other temp CSS field -->
</head>
<body>
<div id="top-menu"></div>

<header id="header">
    <div class="layui-container">
        <div class="layui-row header-top">
            <div class="span5 logo">
                <a class="logo-img" href="/"><img src="/static/pic/logo.png" style="width: 135px" alt="Logo"/></a>
                <p class="tagline"><?= app()->config->get('base.site_description') ?></p>
            </div>
        </div>
    </div>
</header>

<nav id="nav" class="header-nav layui-container">
    <div class="layui-row" align="center">
        <nav id="menu">
            <ul class="layui-nav">
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/">Index</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/forums', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/forums">Forums</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrents', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrents">Torrents</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrents/request', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrents/request">Request</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrents/upload', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrents/upload">Upload</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/subtitles', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/subtitles">Subtitles</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/site/topten', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/site/topten">Top 10</a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/site/about', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/site/about">About</a></li>
            </ul>
        </nav>
    </div>
    <div class="layui-row header-info">
        <div class="pull-left">
            Welcome Back, <a href="/user"><?= app()->user->getUsername() ?></a>&nbsp;
            <span><!--suppress HtmlUnknownTarget --><a href="/auth/logout">[Logout]</a></span>&nbsp;
            <?php if (app()->user->getClass(true) > \Rid\User\UserInterface::ROLE_FORUM_MODERATOR): ?>
                <span><!--suppress HtmlUnknownTarget --><a href="/admin">[Admin Panel]</a></span>&nbsp;
            <?php endif; ?>
            <span><!--suppress HtmlUnknownTarget --><a href="/torrents/favour">[Favour]</a></span>&nbsp;
            <br>
            <span>Ratio: <?= app()->user->getRatio() ?></span>&nbsp;
            <span>Uploaded: <?= $this->e(app()->user->getUploaded(), 'format_bytes') ?></span>&nbsp;
            <span>Downloaded: <?= $this->e(app()->user->getDownloaded(), 'format_bytes') ?></span>&nbsp;
            <span>BT Activity:
                <span class="fas fa-arrow-up icon-seeding"></span>&nbsp;<?= app()->user->getActiveSeed() ?>&nbsp;
                <span class="fas fa-arrow-down icon-leeching"></span>&nbsp;<?= app()->user->getActiveLeech() ?>&nbsp;
            </span>&nbsp;
        </div>
        <div class="pull-right">
            <?php //TODO right information ?>
        </div>
    </div>
</nav> <!-- /nav -->


<div id="container" class="layui-container">
    <?= $this->section('container') ?> <!-- Page Content -->
</div> <!-- /container -->

<footer id="footer-menu">
    <div class="container" align="center">
        <div class="row">
            <p class="copyright">
                <a href="/" target="_self"><?= app()->config->get('base.site_name') ?></a> 2019-2020 Powered by <a href="https://github.com/Rhilip/RidPT">RidPT</a>
            </p>
            <p class="create-info">[ Page created in <b>{{ cost_time|number_format(5) }}</b> sec with <b><?= count(app()->pdo->getExecuteData()) ?></b> db queries, <b><?= array_sum(app()->redis->getCalledData())?></b> calls of Redis ]</p>
        </div>
    </div>
</footer>

<script src="/lib/layui/src/layui.js"></script>
<script src="/static/js/main.js?<?= app()->config->get('base.site_css_update_date') ?>"></script>
<?= $this->section('script') ?> <!-- Other temp script field -->

</body>
</html>
