<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/13
 * Time: 8:57
 *
 * @var League\Plates\Template\Template $this
 * @var string $msg
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="jumbotron">
    <h1>Opps~</h1>
    <p><?= nl2br($msg) ?></p>
</div>
<?php $this->end(); ?>
