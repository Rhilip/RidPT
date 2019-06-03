<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:44
 *
 * @var League\Plates\Template\Template $this
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="jumbotron">
    <h1>Your account is success Confirmed.</h1>
    <p>Click <!--suppress HtmlUnknownTarget --><a href="/auth/login">Login Page</a> to login</p>
</div>
<?php $this->end(); ?>

<?php $this->push('script') ?>
<script>
    window.setTimeout(function () {
        location.href = '/auth/login';
    }, 2000);
</script>
<?php $this->end(); ?>
