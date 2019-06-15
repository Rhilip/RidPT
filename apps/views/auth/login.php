<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:32
 *
 * @var League\Plates\Template\Template $this
 * @var int $left_attempts
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-7 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading">Authenticate</div>
            <div class="panel-body">
                <div class="text-primary text-center">
                    <strong>You have <?= $left_attempts > 3 ? $left_attempts : "<span class='text-red'>$left_attempts</span>" ?>/<?= app()->config->get('security.max_login_attempts') ?> attempts left, or your IP will be banned.</strong>
                </div>
                <?php if (isset($error_msg)): ?>
                <div class="text-danger text-center">
                    Login failed: <strong class="text-center"><?= $error_msg ?></strong>
                </div>
                <?php endif; ?>
                <form class="auth-form" method="post" data-toggle="validator" role="form">
                    <div class="form-group">
                        <label for="username">Username / Email address</label>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="fas fa-user-alt fa-fw"></span></span>
                            <input type="text" class="form-control" id="username" name="username" required
                                   placeholder="" value="<?= $username ?? '' ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="pull-right"><a href="/auth/recover" class="text-muted">Forget you password?</a></div> <!-- TODO password recover -->
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

                    <div class="form-group">
                        <label for="captcha">Captcha</label>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon"><span class="fas fa-sync-alt fa-fw"></span></span>
                                    <input type="text" class="form-control" id="captcha" name="captcha" maxlength="6" required
                                           placeholder="" autocomplete="off">
                                </div>
                                <div class="help-block">Case insensitive.</div>
                            </div>
                            <?= $this->insert('layout/captcha') ?>
                        </div>
                    </div>

                    <fieldset>
                        <legend><a href="#adv_option" data-toggle="collapse" class="btn btn-link">Advanced Options</a></legend>
                        <div id="adv_option" class="collapse">
                            <div class="row">
                                <label for="logout" class="col-md-3">Auto Logout</label>
                                <div class="col-md-6">
                                    <input type="checkbox"  name="logout" id="logout" value="yes" title=""> Log me out after 15 minutes
                                </div>
                            </div>
                            <div class="row">
                                <label for="securelogin" class="col-md-3">Restrict IP</label>
                                <div class="col-md-6">
                                    <input type="checkbox"  name="securelogin" id="securelogin" value="yes" title="">Restrict session to my IP
                                </div>
                            </div>
                            <div class="row">
                                <label for="ssl" class="col-md-3">SSL (HTTPS)</label>
                                <div class="col-md-6">
                                    <input type="checkbox"  name="ssl" id="ssl" value="yes" title=""<?= app()->request->isSecure() ? ' checked disabled': '' ?>> Enable SSL
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
