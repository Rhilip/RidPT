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

$css_tag = env('APP_DEBUG') ? time() : config('base.site_css_update_date');
$extend_debug_info = app()->auth->getCurUser()  // Not Anonymous
    && config('base.enable_extend_debug')  // Enabled Extend Debug
    && app()->auth->getCurUser()->isPrivilege('see_extend_debug_log');  // Privilege is enough
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="referrer" content="same-origin" />
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">

    <meta name="author" content="<?= $this->e(config('base.site_author')) ?>">
    <meta name="generator" content="<?= $this->e(config('base.site_generator')) ?>">
    <meta name="keywords" content="<?= $this->e(config('base.site_keywords')) ?>">
    <meta name="description" content="<?= $this->e(config('base.site_description')) ?>">
    <meta name="copyright" content="<?= $this->e(config('base.site_copyright')) ?>">

    <script type="text/javascript">const _head_start = new Date();</script>

    <!-- ICON of favicon.ico -->
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <!-- Other meta field -->
    <?= $this->section('meta') ?>

    <title><?= config('base.site_name') ?> :: <?= $this->e($this->section('title') ?? '') ?> -- Powered by <?= config('base.site_generator') ?></title>

    <!-- styles of Library -->
    <link rel="stylesheet" href="/lib/flag-css/dist/css/flag-css.min.css">
    <link rel="stylesheet" href="/lib/fontAwesome/css/all.min.css">
    <link rel="stylesheet" href="/lib/zui/dist/css/zui.min.css">
    <link rel="stylesheet" href="/lib/jqjquery-wysibb/theme/default/wbbtheme.css">

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
                <p class="tagline"><?= config('base.site_description') ?></p>
            </div>
        </div>
    </header>
    <div class="clearfix"></div>

    <?php if (app()->auth->getCurUser() === false): ?>
        <?= $this->insert('layout/nav_anonymous') ?>
    <?php else: ?>
        <?= $this->insert('layout/nav_user') ?>
    <?php endif; ?>
    <div class="clearfix"></div>

    <div id="container" class="container main-container">
        <?= $this->section('container') ?> <!-- Page Content -->
    </div> <!-- END /container -->
    <div class="clearfix"></div>
</div>

<footer id="footer_menu">
    <div class="container text-center">
        <div class="row">
            <p class="copyright">
                &copy; <a href="/" target="_self"><?= config('base.site_name') ?></a> 2019-2020 Powered by <a href="https://github.com/Rhilip/RidPT" target="_blank">RidPT</a>
            </p>
            <p class="debug-info">
                [ Page created in <b><?= number_format(microtime(true) - app()->request->start_at, 6) ?></b> sec
                with <b><?= $this->e(memory_get_usage(), 'format_bytes') ?></b> ram used,
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

<div id="fixbar">
    <button class="btn" type="button" id="scroll_top"><i class="fas fa-angle-double-up"></i> Scroll Top</button>
</div>

<?= $this->section('body') ?>

<!-- noscript alert -->
<noscript>
    <style type="text/css">
        body > div, footer {display:none;}
    </style>
    <div class="noscriptmsg">
        You don't have javascript enabled.
    </div>
</noscript>

<!-- Javascript of Library -->
<script src="/lib/localforage/dist/localforage.min.js"></script>
<script src="/lib/jquery/dist/jquery.min.js"></script>
<script src="/lib/zui/dist/js/zui.min.js"></script>
<script src="/lib/jqjquery-wysibb/jquery.wysibb.js"></script>
<script src="/lib/bootstrap-validator/dist/validator.min.js"></script>
<script src="/lib/jquery.textarea.autoresize/js/jquery.textarea.autoresize.js"></script>

<!-- Custom Javascript of this template -->
<script src="/static/js/main.js?<?= $css_tag ?>"></script>

<!-- Other Page script field -->
<?= $this->section('script') ?>
</body>
</html>
