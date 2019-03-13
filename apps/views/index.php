<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 11:38
 *
 * @var League\Plates\Template\Template $this
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Index<?php $this->end();?>

<?php $this->start('container') ?>
    <!-- Main component for a primary marketing message or call to action -->
    <div class="jumbotron">
        <h1>Navbar <?= __('greeting',null,'zh-CN'); ?></h1>
        <p><strong>I'm sorry for broken page since I'm rebuilding. <?= __('greet') ?></strong></p>
    </div>
<?php $this->stop() ?>
