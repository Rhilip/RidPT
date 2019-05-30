<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 11:38
 *
 * Some Javascript Library Documents:
 *   - flag-css: https://kfpun.com/flag-css/
 *   - fontAwesome : https://fontawesome.com/icons?d=gallery
 *   - Zui: http://zui.sexy/
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

    <!-- styles of Library -->
    <link rel="stylesheet" href="/lib/flag-css/dist/css/flag-css.min.css">
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.min.css">
    <link rel="stylesheet" href="/lib/zui/dist/css/zui.min.css">

    <!-- Custom styles of this template -->
    <link rel="stylesheet" href="/static/css/main.css?<?= $css_tag ?>">

    <!-- Other Page CSS field -->
    <?= $this->section('css') ?>
</head>
<body>
<script type="text/javascript">const _body_start = new Date();</script>
<div id="top_menu"></div>

<div class="container">
    <header id="header">
        <div class="row header-top">
            <div class="span5 logo">
                <a class="logo-img" href="/"><img src="/static/pic/logo.png" style="width: 135px" alt="Logo"/></a>
                <p class="tagline"><?= app()->config->get('base.site_description') ?></p>
            </div>
        </div>
    </header>
    <div class="clearfix"></div>

    <nav id="nav" class="navbar navbar-default navbar-static-top navbar-custom" role="navigation">
        <div class="container">
            <div class="collapse navbar-collapse navbar-collapse-custom">
                <ul class="nav navbar-nav nav-justified">
                    <li<?= $this->uri('/index', ' class="active"') ?>><a href="/"><?= __('nav_index') ?></a></li>
                    <li<?= $this->uri('/forums', ' class="active"'); ?>><a href="/forums"><?= __('nav_forums') ?></a></li>
                    <li<?= $this->uri('/torrents', ' class="active"'); ?>><a href="/torrents"><?= __('nav_torrents') ?></a></li>
                    <li<?= $this->uri('/torrent/upload', ' class="active"'); ?>><a href="/torrent/upload"><?= __('nav_upload') ?></a></li>
                    <li<?= $this->uri('/torrents/request', ' class="active"'); ?>><a href="/torrents/request"><?= __('nav_requests') ?></a></li>
                    <li<?= $this->uri('/subtitles', ' class="active"'); ?>><a href="/subtitles"><?= __('nav_subtitles') ?></a></li>
                    <li<?= $this->uri('/site/rules', ' class="active"'); ?>><a href="/site/rules"><?= __('nav_rules') ?></a></li>
                    <li<?= $this->uri('/site/staff', ' class="active"'); ?>><a href="/site/staff"><?= __('nav_staff') ?></a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?= __('nav_more') ?> <b class="caret"></b></a>
                        <ul class="dropdown-menu" role="menu">
                            <li<?= $this->uri('/site/topten', ' class="active"'); ?>><a href="/site/topten"><?= __('nav_topten') ?></a></li>
                            <li class="divider"></li>
                            <li<?= $this->uri('/site/stats', ' class="active"'); ?>><a href="/site/stats"><?= __('nav_stats') ?></a></li>
                            <li<?= $this->uri('/site/log', ' class="active"'); ?>><a href="/site/log"><?= __('nav_log') ?></a></li>
                        </ul>
                    </li>
                </ul> <!-- END .navbar-nav -->
            </div><!-- END .navbar-collapse -->
        </div>
    </nav> <!-- END /nav -->
    <div class="clearfix"></div>

    <div id="info_block">
        <div id="info_block_line_1">
            Welcome Back, <a href="/user" data-user-id="<?= app()->user->getId() ?>"><?= app()->user->getUsername() ?></a>&nbsp;
            <span data-item="logout"><!--suppress HtmlUnknownTarget -->[<a href="/auth/logout">Logout</a>]</span>
            <?php if (app()->user->getClass(true) > \apps\components\User\UserInterface::ROLE_FORUM_MODERATOR): ?>
                <span><!--suppress HtmlUnknownTarget -->[<a href="/admin">Admin Panel</a>]</span>
            <?php endif; ?>
            <span data-item="favour"><!--suppress HtmlUnknownTarget -->[<a href="/torrents/favour">Favour</a>]</span>
            <span data-item="invite" data-invite="">
                <span class="color-invite">Invite [<a href="/user/invite">Send</a>]: </span> 3 <!-- TODO  -->
            </span>
            <span data-item="bonus" data-bonus="">
                <span class="color-bonus">Bonus: </span> <a href="#">2345234</a> <!-- TODO  -->
            </span>
            <span data-item="bet">[<a href="#"><span class="color-bet">Bet(22)</span></a>]</span> <!-- TODO  -->
            <span data-item="blackjack">[<a href="#"><span class="color-blackjack">Blackjack</span></a>]</span> <!-- TODO  -->
            <div class="pull-right">
                <span data-item="chang_lang">
                    <a href="#"><span class="flag flag-chn"></span></a>&nbsp;
                    <a href="#"><span class="flag flag-hkg"></span></a>&nbsp;
                    <a href="#"><span class="flag flag-gbr"></span></a>&nbsp;
                </span> <!-- TODO -->
                Now: <?= date('H:i (P)') ?>
            </div>
        </div>
        <div id="info_block_line_2">
            <span data-item="ratio" data-ratio="<?= $this->e(app()->user->getRatio()) ?>">
                <span class="color-ratio">Ratio:</span> <?= app()->user->getRatio() ?></span>&nbsp;
            <span data-item="uploaded" data-uploaded="<?= $this->e(app()->user->getUploaded()) ?>">
                <span class="color-seeding">Uploaded:</span> <?= $this->e(app()->user->getUploaded(), 'format_bytes') ?>
            </span>&nbsp;
            <span data-item="download" data-downloaded="<?= $this->e(app()->user->getDownloaded()) ?>">
                <span class="color-leeching">Downloaded:</span> <?= $this->e(app()->user->getDownloaded(), 'format_bytes') ?>
            </span>&nbsp;
            <span data-item="bt_activity" data-seeding="<?= app()->user->getActiveSeed() ?>" data-leeching="<?= app()->user->getActiveLeech() ?>">
                BT Activity:
                <span class="fas fa-arrow-up fa-fw color-seeding"></span>&nbsp;<?= app()->user->getActiveSeed() ?>&nbsp;
                <span class="fas fa-arrow-down fa-fw color-leeching"></span>&nbsp;<?= app()->user->getActiveLeech() ?>&nbsp;
            </span>
            <span data-item="connectable" data-connectable="IPv4/IPv6">
                <span class="color-connectable">Connectable:</span>
                IPv4/IPv6 <!-- TODO -->
            </span>
            <div class="pull-right">
                <a href="/user/message"><span class="fas fa-envelope fa-fw red"></span>Message Box (15)</a> <!-- TODO -->
                <a href="#"><span class="fas fa-user-friends fa-fw color-friends"></span></a> <!-- TODO -->
                <a href="#"><span class="fas fa-rss-square fa-fw color-rss"></span></a>  <!-- TODO -->
            </div>
        </div>
    </div> <!-- END /info_block -->
    <div class="clearfix"></div>

    <div id="container" class="container main-container">
        <?= $this->section('container') ?> <!-- Page Content -->
    </div> <!-- END /container -->
    <div class="clearfix"></div>
</div>

<footer id="footer_menu">
    <div class="container" align="center">
        <div class="row">
            <p class="copyright">
                &copy; <a href="/" target="_self"><?= app()->config->get('base.site_name') ?></a> 2019-2020 Powered by <a href="https://github.com/Rhilip/RidPT">RidPT</a>
            </p>
            <p class="create-debug-info">[ Page created in <b><?= number_format(microtime(true) - app()->request->start_at, 6) ?></b> sec with <b><?= $this->e(memory_get_usage(),'format_bytes') ?></b> ram used, <b><?= count(app()->pdo->getExecuteData()) ?></b> db queries, <b><?= array_sum(app()->redis->getCalledData())?></b> calls of Redis ]</p>
        </div>
    </div>
</footer>

<!-- Javascript of Library -->
<script src="/lib/jquery/dist/jquery.min.js"></script>
<script src="/lib/zui/dist/js/zui.min.js"></script>

<!-- Custom Javascript of this template -->
<script src="/static/js/bbcodeParser.js"></script>
<script src="/static/js/main.js?<?= $css_tag ?>"></script>

<!-- Other Page script field -->
<?= $this->section('script') ?>
</body>
</html>
