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
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="referrer" content="same-origin" />

    <meta name="author" content="<?= app()->config->get('base.site_author') ?>">
    <meta name="generator" content="<?= app()->config->get('base.site_generator') ?>">
    <meta name="keywords" content="<?= app()->config->get('base.site_keywords') ?>">
    <meta name="description" content="<?= app()->config->get('base.site_description') ?>">
    <meta name="copyright" content="<?= app()->config->get('base.site_copyright') ?>">

    <title><?= app()->config->get('base.site_name') ?> :: <?= $this->e($this->section('title') ?? '') ?> -- Powered by RidPT</title>

    <!-- ICON of favicon.ico -->
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <link rel="stylesheet" href="/lib/layui/src/css/layui.css"> <?php /** https://www.layui.com/doc/ */ ?>
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.css"> <?php /** https://fontawesome.com/icons?d=gallery */ ?>

    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="/static/css/main.css?<?= time() // FIXME For debug ?>">

    <?= $this->section('css') ?> <!-- Other temp CSS field -->
</head>
<body>
<?= $this->insert('layout/header') ?>

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

<?= $this->insert('layout/footer') ?>

<script src="/lib/layui/src/layui.js"></script>
<script src="/static/js/main.js?<?= time() // FIXME For debug ?>"></script>
<?= $this->section('script') ?> <!-- Other temp script field -->

</body>
</html>
