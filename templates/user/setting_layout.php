<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 17:55
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Personal settings</h3>
            </div>
            <div class="panel-body">
                <ul class="nav nav-pills nav-stacked">
                    <li><!--suppress HtmlUnknownTarget --><a href="/user/sessions">Sessions</a></li>
                </ul>
            </div><!--/.panel-body -->
        </div>
    </div>
    <div class="col-md-9">
        <?= $this->section('panel') ?>
    </div>
</div>
<?php $this->stop() ?>
