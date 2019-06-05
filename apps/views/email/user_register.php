<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/5
 * Time: 16:24
 *
 * @var League\Plates\Template\Template $this
 * @var string $username
 * @var string $confirm_url
 */
?>

<?php $this->layout('email/layout') ?>

<?php $this->start('username')?><?= $username ?? 'User' ?><?php $this->end();?>

<?php $this->start('subject')?>Register Confirm<?php $this->end();?>
<?php $this->start('body')?>
Thank you for signing up with us. <br>
Your new account has been setup in pending status and you should confirm your account by click this link: <br>
<br>
<a href="<?= $confirm_url ?>" target="_blank"><?= $confirm_url ?></a>
<?php $this->end();?>
