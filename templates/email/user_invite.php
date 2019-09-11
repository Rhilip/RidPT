<?php
/**
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/6
 * Time: 22:44
 *
 * @var League\Plates\Template\Template $this
 * @var string $username
 * @var string $invite_link
 */
?>

<?php $this->layout('email/layout') ?>

<?php $this->start('username')?><?= $username ?? 'User' ?><?php $this->end();?>

<?php $this->start('subject')?>Invite<?php $this->end();?>
<?php $this->start('body')?>
You are asking for recover your password in our Site,  <br>
IF this is what you want , please click this link to sign up an account in our system:  <br>

<br>
<a href="<?= $invite_link ?>" target="_blank"><?= $invite_link ?></a>
<?php $this->end();?>

