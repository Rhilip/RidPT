<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 21:46
 *
 * @var League\Plates\Template\Template $this
 * @var string $message
 */
?>

<?= $this->layout('layout/base') ?>
<?php $this->start('title')?><?= $message ?><?php $this->end();?>

<?php $this->start('container')?>
<h1><span><?= $message ?></span></h1>
<?php $this->end();?>
