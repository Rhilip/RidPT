<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:58
 *
 * @var League\Plates\Template\Template $this
 */

$register_type = app()->request->get('type', 'open')
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-7 col-md-offset-3">
        <div class="panel">
            <div class="panel-heading">Recruitment</div>
            <div class="panel-body">
                <?php if (app()->config->get('base.enable_register_system') != true): ?>
                    <h2>Sorry~</h2>
                    <p class="lead">
                        The register system is close.
                    </p>
                <?php else: ?>
                    <?php if (in_array(strtolower($register_type), ['invite', 'green', 'open'])): ?>
                        <?php if (app()->config->get('register.by_' . $register_type) != true): ?>
                            <h2>Sorry ~</h2>
                            <p>
                                Our registration is currently disabled. If you are lucky you might have a friend who wants to invite
                                you :) We just wanna see how much cheaters will start respecting their accounts after they realize
                                they can't just come back in and get another one :). Keep this in mind, if you are already a member
                                and you invite a known cheater, and you knew about it in the first place, both yours and the person
                                you invited are disabled. You will have to come talk to us to get your account reenstated. If you
                                want an invite and you know someone who have one it's up to them to give you an invite.
                            </p>
                        <?php else: ?>
                            <form class="auth-form" method="post">
                                <label for="type"></label><input name="type" id="type" value="<?= $register_type ?>" style="display: none">

                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fas fa-user-alt fa-fw"></span></span>
                                        <input type="text" class="form-control" id="username" name="username" required
                                               value="<?= $username ?? '' ?>">
                                    </div>
                                    <div class="help-block">Max Length 12 with those character: <code>A-Za-z0-9_</code></div>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fas fa-envelope fa-fw"></span></span>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="help-block">We only allow those Email: <code><?= app()->config->get('register.email_white_list') ?></code></div>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <div class="pull-right" id="password_strength" style="display: none">
                                        Strength: <span id="password_strength_text"></span>
                                    </div> <!-- TODO add password strength check -->
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fas fa-key fa-fw"></span></span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button id="password_help_btn" type="button" class="btn btn-link auth-password-help-btn"><i class="fas fa-eye fa-fw"></i></button>
                                    </div>
                                    <div class="help-block" id="password_strength_suggest"></div>
                                </div>

                                <div class="form-group">
                                    <label for="password_again">Retype Password</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fas fa-key fa-fw"></span></span>
                                        <input type="password" class="form-control" id="password_again" name="password_again" required>
                                    </div>
                                </div>

                                <?php if ($register_type == 'invite') : ?>
                                    <?php $invite_hash = app()->request->get('invite_hash', '') ?>
                                <div class="form-group">
                                    <label for="invite_hash">Invite Code</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><span class="fas fa-code fa-fw"></span></span>
                                        <input type="text" class="form-control" id="invite_hash" name="invite_hash"
                                               value="<?= $invite_hash ?>" required>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="captcha">Captcha</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="input-group">
                                                <span class="input-group-addon"><span class="fas fa-sync-alt fa-fw"></span></span>
                                                <input type="text" class="form-control" id="captcha" name="captcha" maxlength="6"
                                                       required autocomplete="off">
                                            </div>
                                            <div class="help-block">Case insensitive.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <?= $this->insert('layout/captcha') ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="verify_tos" id="verify_tos" value="yes" title=""> Accept Our TOS and AOP
                                        </label>
                                    </div>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="verify_age" id="verify_age" value="yes" title=""> I am at least 13 years old.
                                        </label>
                                    </div>
                                </div>

                                <div class="text-center">
                                    <button type="submit" value="Register" class="btn btn-primary">Register</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

<?php $this->start('script'); ?>
<script src="/lib/zxcvbn/dist/zxcvbn.js" async></script>
<?php $this->end(); ?>
