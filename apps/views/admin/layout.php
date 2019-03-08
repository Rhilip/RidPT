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
<div class="layui-row">
    <div class="layui-col-md3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Site Status</h3>
            </div>
            <div class="panel-body">
                <ul class="nav nav-pills nav-stacked">
                    <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=mysql">Mysql Status</a></li>
                    <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=redis">Redis Service Status</a></li>
                    <li><!--suppress HtmlUnknownTarget --><a href="/admin/service?provider=redis&panel=keys">Redis Keys Status</a></li>
                </ul>
            </div><!--/.panel-body -->
        </div>
    </div>
    <div class="layui-col-md9">
        <?= $this->section('panel') ?>
    </div>
</div>
<?php $this->end();?>

