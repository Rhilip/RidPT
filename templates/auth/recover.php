<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/3
 * Time: 23:10
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Authorization Point<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-7 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading">Recover lost user name or password</div>
            <div class="panel-body">
                <fieldset class="auth-recover-step" data-step="1">
                    <legend class="text-special">1. Enter Your Email</legend>

                    <form class="auth-form" method="post">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fas fa-envelope fa-fw"></span></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="help-block">The Email when your sign account.</div>
                        </div>

                        <?= $this->insert('layout/captcha') ?>

                        <div class="text-center">
                            <button type="submit" value="Register" class="btn btn-primary">Recover it !!</button>
                        </div>
                    </form>
                </fieldset>
                <fieldset class="auth-recover-step" data-step="2"><legend>2. Follow The Link in confirmation email to reset your password.</legend></fieldset>
                <fieldset class="auth-recover-step" data-step="3"><legend>3. Get new generate password from Email and login.</legend></fieldset>
                <fieldset class="auth-recover-step" data-step="3"><legend>4. Set your own password in user panel.</legend></fieldset>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>
