<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:46
 *
 * @var League\Plates\Template\Template $this
 * @var string $confirm_way
 * @var string $email
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Confirm Your Account<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="jumbotron">
    <h1>One more Step</h1>
    <?php if ($confirm_way == 'email'): ?>
        <p>Check your email : <?= $email ?> to confirm your account.</p>
    <?php elseif ($confirm_way == 'mod'): ?>
        <p>Please Wait Our Mod to confirm your account.</p>
    <?php endif; ?>
</div>
<?php $this->end(); ?>

