<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 15:08
 *
 * @var League\Plates\Template\Template $this
 */
?>

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
