<?php
/**
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:44
 *
 * @var League\Plates\Template\Template $this
 * @var string $action 'register'|'recover'
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="jumbotron">
    <?php if ($action == 'register'): ?>
    <h1>Your account is success Confirmed.</h1>
    <?php elseif ($action == 'recover'): ?>
    <h1>Your password has been reset and new password has been send to your email, Please find it and login.</h1>
    <?php endif; ?>
    <p>Click <!--suppress HtmlUnknownTarget --><a href="/auth/login">Login Page</a> to login, Or wait 5 seconds to auto redirect.</p> <!-- TODO wait seconds change -->
</div>
<?php $this->end(); ?>

<?php $this->push('script') ?>
<script>
    window.setTimeout(function () {
        location.href = '/auth/login';
    }, 5e3);
</script>
<?php $this->end(); ?>
