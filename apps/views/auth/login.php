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

<?php $this->start('panel') ?>
<h1>Authenticate</h1>

<form class="layui-form layui-form-pane auth-form" method="post">
    <div class="layui-form-item">
        <label class="layui-form-label" for="username"><i class="layui-icon layui-icon-username"></i></label>
        <div class="layui-input-block">
            <input  type="text" class="layui-input" id="username" name="username" required  lay-verify="required" placeholder="Username / Email address" title="" value="<?= $username ?? '' ?>">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" for="password"><i class="layui-icon layui-icon-password"></i></label>
        <div class="layui-input-block">
            <input type="password" class="layui-input" id="password" name="password" required lay-verify="required" placeholder="Password" autocomplete="off">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label" for="opt"><i class="layui-icon layui-icon-cellphone"></i></label>
        <div class="layui-input-block">
            <input type="text" class="layui-input" id="opt" name="opt" placeholder="2FA Code" maxlength="6" autocomplete="off">
        </div>
        <p class="auth-form-notify">Your 2FA code, leave it blank if you haven't enable 2FA.</p>
    </div>
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
        <label for="logout"></label><input type="checkbox" name="logout" id="logout" value="yes" title="Auto Logout">
        <label for="securelogin"></label><input type="checkbox" name="securelogin" id="securelogin" value="yes" title="Lock Session IP">
        <label for="ssl"></label><input type="checkbox" name="ssl" id="ssl" value="yes" title="Enable SSL">
    </div>
    <hr>

    <div class="layui-form-item">
        <button type="submit" value="Login" class="layui-btn layui-btn-normal layui-btn-fluid">Login</button>
    </div>

    <div class="auth-attempts-msg" align="center">
        <strong>You have <?= $left_attempts > 3 ? $left_attempts : "<span style='color: #FF5722'>$left_attempts</span>" ?>/<?= app()->config->get('security.max_login_attempts') ?> attempts left, or your IP will be banned.</strong>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="auth-error-msg">
            <p style="color: #FF5722">Login failed: <strong class="text-center"><?= $error_msg ?></strong></p>
        </div>
    <?php endif; ?>
</form>
<?php $this->end(); ?>
