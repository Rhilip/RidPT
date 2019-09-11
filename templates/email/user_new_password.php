<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/5
 * Time: 18:57
 *
 * @var League\Plates\Template\Template $this
 * @var string $username
 * @var string $password
 */
?>

<?php $this->layout('email/layout') ?>

<?php $this->start('username')?><?= $username ?? 'User' ?><?php $this->end();?>

<?php $this->start('subject')?>Recover Confirm<?php $this->end();?>
<?php $this->start('body')?>
Your password in Our Site is successful update .<br>
<br>
The new password: <code><?= $password ?></code><br>
<br>
Please login and reset your password. <br>
<?php $this->end();?>
