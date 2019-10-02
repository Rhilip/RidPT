<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 22:37
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Entity\Torrent $torrent
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Admin Panel<?php $this->end();?>

<?php $this->start('container')?>
<div class="row admin-panel">
    <div class="col-md-2">
        <h3>..:: For SysOp Only ::..</h3>
        <nav class="admin-panel-nav-sysop" data-ride="menu">
            <ul id="admin-panel-menu-sysop" class="tree tree-menu" data-ride="tree">
                <li class="open">
                    <a href="#"><i class="icon icon-time"></i>Site Status</a>
                    <ul>
                        <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=mysql"><cite>Mysql Status</cite></a></li>
                        <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=redis"><cite>Redis Service Status</cite></a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <div class="col-md-10">
        <div class="admin-panel-content">
            <?= $this->section('panel') ?>
        </div>
    </div>
</div>

<?php $this->end();?>

