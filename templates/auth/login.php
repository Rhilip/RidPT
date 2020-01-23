<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:32
 *
 * @var League\Plates\Template\Template $this
 * @var int $test_attempts
 */

$left_attempts = config('security.max_login_attempts') - ($test_attempts ?? 0);
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Authorization Point<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-7 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading"><?= __('nav.authenticate') ?></div>
            <div class="panel-body">
                <div class="text-primary text-center">
                    <strong>You have <?= $left_attempts > 3 ? $left_attempts : "<span class='text-red'>$left_attempts</span>" ?>/<?= config('security.max_login_attempts') ?> attempts left, or your IP will be banned.</strong>
                </div>
                <?php if (isset($error_msg)): ?>
                <div class="text-danger text-center">
                    Login failed: <strong class="text-center"><?= $error_msg ?></strong>
                </div>
                <?php endif; ?>
                <form class="auth-form" method="post" data-toggle="validator" role="form">
                    <div class="form-group">
                        <label for="username"><?= __('form.login.username') ?></label>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fas fa-user-alt fa-fw"></span></span>
                            <input type="text" class="form-control" id="username" name="username" required
                                   placeholder="" value="<?= $this->e(app()->request->request->get('username', '')) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password"><?= __('form.password') ?></label>
                        <div class="pull-right"><a href="/auth/recover" class="text-muted">Forget you password?</a></div>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fas fa-key fa-fw"></span></span>
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="" autocomplete="off">
                            <button id="password_help_btn" type="button" class="btn btn-link auth-password-help-btn"><i class="fas fa-eye fa-fw"></i></button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="opt">2FA Code</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fas fa-shield-alt fa-fw"></i></span>
                            <input type="text" class="form-control" id="opt" name="opt" maxlength="6"
                                   placeholder="" autocomplete="off">
                        </div>
                        <div class="help-block">Your 2FA code, leave it blank if you haven't enable 2FA.</div>
                    </div>

                    <?= $this->insert('layout/captcha') ?>

                    <fieldset>
                        <legend><a href="#adv_option" data-toggle="collapse" class="btn btn-link">Advanced Options</a></legend>
                        <div id="adv_option" class="collapse">
                            <div class="form-group">
                                <?php // -1 - disable -> 'disabled' ; 0 - option -> '' ; 1 - option but default checked -> 'checked' ; 2 - force -> 'checked disabled'?>
                                <div class="switch">
                                    <input type="checkbox" name="logout" id="logout" value="yes"
                                           <?php if (config('security.auto_logout') > 0): ?>checked<?php endif; ?>
                                           <?php if (in_array(config('security.auto_logout'), [-1, 2])): ?>disabled<?php endif; ?>
                                    >
                                    <label for="logout">Automatically Log me out after 15 minutes</label>
                                </div>
                                <div class="switch">
                                    <input type="checkbox" name="securelogin" id="securelogin" value="yes"
                                           <?php if (config('security.secure_login') > 0): ?>checked<?php endif; ?>
                                           <?php if (in_array(config('security.secure_login'), [-1, 2])): ?>disabled<?php endif; ?>
                                    >
                                    <label for="securelogin">Restrict session to my login IP</label>
                                </div>
                                <div class="switch">
                                    <input type="checkbox" name="ssl" id="ssl" value="yes"
                                           <?php if (app()->request->isSecure() || config('security.ssl_login') > 0): ?>checked<?php endif; ?>
                                           <?php if (app()->request->isSecure() || in_array(config('security.ssl_login'), [-1, 2])): ?>disabled<?php endif; ?>
                                    >
                                    <label for="ssl">Enable SSL (HTTPS)</label>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>
