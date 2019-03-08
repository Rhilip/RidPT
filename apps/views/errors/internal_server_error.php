<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 21:41
 *
 * @var League\Plates\Template\Template $this
 * @var string $message
 * @var string $file
 * @var string $type
 * @var string $code
 * @var string $line
 * @var string $trace
 */
?>

<?= $this->layout('layout/base') ?>
<?php $this->start('title')?><?= $message ?><?php $this->end();?>

<?php $this->start('container')?>
<h1><span><?= $message ?></span></h1>
<?php if ($file): ?>
<p><?= $type ?> Code <?= $code ?></p>
<p><span><?= $file ?></span> Line <span><?= $line ?></span></p>
<samp><?= nl2br($trace) ?></samp>
<?php endif; ?>
<?php $this->end();?>
