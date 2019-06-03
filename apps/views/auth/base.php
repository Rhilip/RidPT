<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/9
 * Time: 9:50
 *
 * @var League\Plates\Template\Template $this
 */

$css_tag = env('APP_DEBUG') ? time() : app()->config->get('base.site_css_update_date');
$extend_debug_info = app()->config->get('base.enable_extend_debug') &&
    app()->user->getClass(true) > app()->config->get('authority.see_extend_debug_log');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php $this->insert('layout/head'); ?>

    <title><?= app()->config->get('base.site_name') ?> :: Authorization Point -- Powered by RidPT</title>

    <!-- styles of Library -->
    <link rel="stylesheet" href="/lib/flag-css/dist/css/flag-css.min.css">
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.min.css">
    <link rel="stylesheet" href="/lib/zui/dist/css/zui.min.css">

    <!-- Custom styles of this template -->
    <link rel="stylesheet" href="/static/css/main.css?<?= $css_tag ?>">

    <!-- Other Page CSS field -->
    <?= $this->section('css') ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/lib/html5shiv/dist/html5shiv.min.js"></script>
    <script src="/lib/respond/dest/respond.min.js"></script>
    <![endif]-->
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
        <div class="navbar-header">
            <a class="navbar-brand" href="#"><?= app()->config->get('base.site_name') ?></a>
        </div>
        <div class="collapse navbar-collapse navbar-collapse-custom">
            <ul class="nav navbar-nav">
                <li<?= $this->uri('/auth/login', ' class="active"') ?>><!--suppress HtmlUnknownTarget --><a href="/auth/login">Authenticate</a></li>
                <li<?= $this->uri('/auth/register', ' class="active"'); ?>><!--suppress HtmlUnknownTarget --><a href="/auth/register">Recruit</a></li>
            </ul> <!-- END .navbar-nav -->
        </div><!-- END .navbar-collapse -->
    </nav> <!-- END /nav -->
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
            <p class="debug-info">
                [ Page created in <b><?= number_format(microtime(true) - app()->request->start_at, 6) ?></b> sec
                with <b><?= $this->e(memory_get_usage(),'format_bytes') ?></b> ram used,
                <b><?= count(app()->pdo->getExecuteData()) ?></b> db queries,
                <b><?= array_sum(app()->redis->getCalledData())?></b> calls of Redis ]
                <?php if ($extend_debug_info): ?>
                    <a href="javascript:" id="extend_debug_info"><span class="label label-warning label-outline">Debug info</span></a>
                    <script>
                        const _extend_debug_info = true;
                        const _sql_data = '<?= json_encode(app()->pdo->getExecuteData(), JSON_HEX_APOS) ?>';
                        const _redis_data = '<?= json_encode(app()->redis->getCalledData(), JSON_HEX_APOS) ?>';
                    </script>
                <?php endif; ?>
            </p>
        </div>
    </div>
</footer>

<!-- Javascript of Library -->
<script src="/lib/jquery/dist/jquery.min.js"></script>
<script src="/lib/zui/dist/js/zui.min.js"></script>

<!-- Custom Javascript of this template -->
<script src="/static/js/main.js?<?= $css_tag ?>"></script>

<!-- Other Page script field -->
<?= $this->section('script') ?>
</body>
</html>
