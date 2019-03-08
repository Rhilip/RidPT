<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/8
 * Time: 19:32
 *
 * @var League\Plates\Template\Template $this
 */
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Login<?php $this->end(); ?>

<?php $this->start('container') ?>
    <h1>Login</h1>
    <form method="post">
        <div class="row">
            <div class="form-group col-md-4">
                <?php if (isset($left_attemps) && $left_attemps < 3): ?>
                    <div class="form-group">
                        <p class="bg-danger">Left login attempts: <strong class="text-center"><?= $left_attemps ?></strong></p>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label class="control-label" for="username">Username / Email address</label>
                    <input autofocus="" class="form-control" id="username" name="username" placeholder="Username" tabindex="1" title="" type="text" value="<?= $username ?? '' ?>">
                </div>
                <div class="form-group">
                    <label class="control-label" for="password">Password</label>
                    <small class="float-right"><a href="/auth/recover">I have forgot my password</a></small>
                    <input class="form-control" id="password" name="password" tabindex="2" title="" type="password" value="">
                </div>
                <div class="form-group">
                    <label class="control-label" for="opt">2FA Code</label>
                    <input autofocus="" class="form-control" id="opt" name="opt" placeholder="2FA Code" tabindex="3" title="" type="text" value="" maxlength="6">
                    <small>Your 2FA code, leave it blank if you haven't enable 2FA.</small>
                </div>
                <div class="form-group">
                    <label class="control-label" for="captcha">Captcha</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input autofocus="" class="form-control" id="captcha" name="captcha" tabindex="4" title="" type="text" value="" maxlength="6">
                        </div>
                        <div class="col-md-6"><img src="/captcha" alt="captcha"></div>
                    </div>
                    <small>Case insensitive.</small>
                </div>
                <?php if (isset($error_msg)): ?>
                    <div class="form-group">
                        <p class="bg-danger">Login failed: <strong class="text-center"><?= $error_msg ?></strong></p>
                    </div>
                <?php endif; ?>
                <input type="submit" value="Login" class="btn btn-primary" tabindex="4">
            </div>
        </div>
    </form>
<?php $this->end(); ?>
