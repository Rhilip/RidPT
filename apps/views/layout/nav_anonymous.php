<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/21/2019
 * Time: 8:15 AM
 */
?>

<nav id="nav" class="navbar navbar-default navbar-static-top navbar-custom" role="navigation">
    <div class="navbar-header">
        <a class="navbar-brand" href="#"><?= config('base.site_name') ?></a>
    </div>
    <div class="collapse navbar-collapse navbar-collapse-custom">
        <ul class="nav navbar-nav">
            <li><!--suppress HtmlUnknownTarget --><a href="/auth/login">Authenticate</a></li>
            <li><!--suppress HtmlUnknownTarget --><a href="/auth/register">Recruit</a></li>
        </ul> <!-- END .navbar-nav -->
    </div><!-- END .navbar-collapse -->
</nav> <!-- END /nav -->
<div class="clearfix"></div>
