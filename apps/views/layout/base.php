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

$css_tag = env('APP_DEBUG') ? time() : app()->config->get('base.site_css_update_date');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $this->insert('layout/head'); ?>

    <title><?= app()->config->get('base.site_name') ?> :: <?= $this->e($this->section('title') ?? '') ?> -- Powered by <?= app()->config->get('base.site_generator') ?></title>

    <link rel="stylesheet" href="/lib/layui/dist/css/layui.css"> <?php /** https://www.layui.com/doc/ */ ?>
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.min.css"> <?php /** https://fontawesome.com/icons?d=gallery */ ?>

    <!-- Custom styles for this template -->
    <link rel="stylesheet" href="/static/css/main.css?<?= $css_tag ?>">

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
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/"><?= __('nav_index') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/forums', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/forums"><?= __('nav_forums') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrents', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrents"><?= __('nav_torrents') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrents/request', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrents/request"><?= __('nav_requests') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/torrent/upload', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/torrent/upload"><?= __('nav_upload') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/subtitles', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/subtitles"><?= __('nav_subtitles') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/site/topten', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/site/topten"><?= __('nav_topten') ?></a></li>
                <li class="layui-nav-item<?= /** @noinspection PhpUndefinedMethodInspection */ $this->uri('/site/about', ' layui-this'); ?>"><!--suppress HtmlUnknownTarget --><a href="/site/about"><?= __('nav_faq') ?></a></li>
            </ul>
        </nav>
    </div>
    <div class="layui-row header-info">
        <div class="pull-left">
            Welcome Back, <a href="/user" data-user-id="<?= app()->user->getId() ?>"><?= app()->user->getUsername() ?></a>&nbsp;
            <span data-item="logout"><!--suppress HtmlUnknownTarget --><a href="/auth/logout">[Logout]</a></span>&nbsp;
            <?php if (app()->user->getClass(true) > \Rid\User\UserInterface::ROLE_FORUM_MODERATOR): ?>
                <span><!--suppress HtmlUnknownTarget --><a href="/admin">[Admin Panel]</a></span>&nbsp;
            <?php endif; ?>
            <span data-item="favour"><!--suppress HtmlUnknownTarget --><a href="/torrents/favour">[Favour]</a></span>&nbsp;
            <br>
            <span data-item="ratio" data-ratio="<?= $this->e(app()->user->getRatio()) ?>">
                Ratio: <?= app()->user->getRatio() ?></span>&nbsp;
            <span data-item="uploaded" data-uploaded="<?= $this->e(app()->user->getUploaded()) ?>">
                Uploaded: <?= $this->e(app()->user->getUploaded(), 'format_bytes') ?></span>&nbsp;
            <span data-item="download" data-downloaded="<?= $this->e(app()->user->getDownloaded()) ?>">
                Downloaded: <?= $this->e(app()->user->getDownloaded(), 'format_bytes') ?></span>&nbsp;
            <span data-item="bt_activity" data-seeding="<?= app()->user->getActiveSeed() ?>" data-leeching="<?= app()->user->getActiveLeech() ?>">
                BT Activity:
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
            <p class="create-debug-info">[ Page created in <b><?= number_format(microtime(true) - app()->request->start_at, 6) ?></b> sec with <b><?= $this->e(memory_get_usage(),'format_bytes') ?></b> ram used, <b><?= count(app()->pdo->getExecuteData()) ?></b> db queries, <b><?= array_sum(app()->redis->getCalledData())?></b> calls of Redis ]</p>
        </div>
    </div>
</footer>

<script src="/lib/layui/dist/layui.all.js"></script>
<script src="/static/js/bbcodeParser.js"></script>
<script src="/static/js/main.js?<?= $css_tag ?>"></script>
<?= $this->section('script') ?> <!-- Other temp script field -->

</body>
</html>
