<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 22:37
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\Torrent $torrent
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Admin Panel<?php $this->end();?>

<?php $this->start('container')?>
<div class="layui-row admin-panel">
    <div class="admin-panel-tree">
        <ul class="layui-tree">
            <li><h2>Site Status</h2></li>
            <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=mysql"><cite>Mysql Status</cite></a></li>
            <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=redis"><cite>Redis Service Status</cite></a></li>
            <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=redis&panel=keys"><cite>Redis Keys Status</cite></a></li>
        </ul>
    </div>
    <div class="admin-panel-content">
        <?= $this->section('panel') ?>
    </div>
</div>

<?php $this->end();?>

