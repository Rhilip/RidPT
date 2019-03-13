<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:58
 *
 * @var League\Plates\Template\Template $this
 */
?>

<?= $this->layout('auth/base') ?>

<?php $this->start('panel') ?>
<h1>Recruitment</h1>

<?php $register_type = app()->request->get('type', 'open') ?>

<div class="auth-form">
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
                <form class="layui-form layui-form-pane" method="post">
                    <label for="type"></label><input name="type" id="type" value="<?= $register_type ?>" style="display: none">
                    <div class="layui-form-item">
                        <label class="layui-form-label" for="username"><i class="layui-icon layui-icon-username"></i></label>
                        <div class="layui-input-block">
                            <input type="text" class="layui-input" id="username" name="username" required  lay-verify="required" placeholder="Username" value="<?= $username ?? '' ?>">
                        </div>
                        <p class="auth-form-notify">Max Length 12 with those character: <code>A-Za-z0-9_</code></p>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label" for="email"><i class="layui-icon layui-icon-email">Email</i></label>
                        <div class="layui-input-block">
                            <input type="email" class="layui-input" id="email" name="email" required  lay-verify="required" placeholder="Email">
                        </div>
                        <p class="auth-form-notify">
                            We only allow those Email:
                            <code><?= app()->config->get('register.email_white_list') ?></code>
                        </p>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label" for="password"><i class="layui-icon layui-icon-password"></i></label>
                        <div class="layui-input-block">
                            <input type="password" class="layui-input" id="password" name="password" required  lay-verify="required" placeholder="Password">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label" for="password_again"><i class="layui-icon layui-icon-password"></i></label>
                        <div class="layui-input-block">
                            <input type="password" class="layui-input" id="password_again" name="password_again" required  lay-verify="required" placeholder="Password again">
                        </div>
                    </div>

                    <?php if ($register_type == 'invite') : ?>
                        <?php $invite_hash = app()->request->get('invite_hash', '') ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label" for="invite_hash"><i class="layui-icon layui-icon-fonts-code"></i></label>
                            <div class="layui-input-block">
                                <input type="text" class="layui-input" id="invite_hash" name="invite_hash" required  lay-verify="required" placeholder="Invite Code" value="<?= $invite_hash ?>">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label" for="captcha"><i class="layui-icon layui-icon-vercode"></i></label>
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input" id="captcha" name="captcha" placeholder="Captcha" autocomplete="off" maxlength="6">
                            </div>
                            <div class="layui-input-inline" style="width: 150px"><?= $this->insert('layout/captcha') ?></div>
                            <p class="auth-form-notify">Case insensitive.</p>
                        </div>
                    </div>

                    <div class="layui-form-item" align="center">
                        <label for="accept_tos"></label><input type="checkbox" name="accept_tos" id="accept_tos" value="yes" checked title="Accept Our TOS and AOP">
                    </div>

                    <hr>

                    <div class="layui-form-item">
                        <button type="submit" value="Register" class="layui-btn layui-btn-normal layui-btn-fluid">Register</button>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php $this->end(); ?>
