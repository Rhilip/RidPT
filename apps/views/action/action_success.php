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

<?php $this->start('title')?><?= $title ?? 'Action Success' ?><?php $this->end();?>

<?php $this->start('container')?>
<!-- Main component for a primary marketing message or call to action -->
<div class="jumbotron">
    <h1>Update Success!</h1>
    <p><?= nl2br($msg ?? 'Please Wait 5 seconds to automa flush') ?></p>
</div>
<?php $this->end();?>
