<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 21:36
 *
 * @var League\Plates\Template\Template $this
 * @var string $title
 * @var string $msg
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?><?= $title ?? 'Failed Action' ?><?php $this->end();?>

<?php $this->start('container')?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1>Opps~</h1>
    <p><?= nl2br($msg ?? 'Empty Failed Reason, Please Ask the sysop team to fix it.') ?></p>
</div>
<?php $this->end();?>
