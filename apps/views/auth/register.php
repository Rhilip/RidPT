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

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Register<?php $this->end(); ?>

<?php $this->start('container') ?>
<h1>Register</h1>

<?php if (app()->config->get('base.enable_register_system') != true): ?>
    <div class="jumbotron">
        <h2>Sorry~</h2>
        <p class="lead">
            The register system is close.
        </p>
    </div>
<?php else: ?>
    <?php $register_type = app()->request->get('type', 'open') ?>
    <?php if (in_array(strtolower($register_type), ['invite', 'green', 'open'])): ?>
        <?php if (app()->config->get('register.by_' . $register_type) != true): ?>
            <div class="jumbotron">
                <h2>Sorry ~</h2>
                <p>
                    Open registration is currently disabled. Invites only. If you are lucky you might have a
                    friend who wants to invite you :) We just wanna see how much cheaters will start respecting
                    their accounts after they realize they can't just come back in and get another one :). Keep
                    this in mind, if you are already a member and you invite a known cheater, and you knew about
                    it in the first place, both yours and the person you invited are disabled. You will have to
                    come talk to us to get your account reenstated. If you want an invite and you know someone
                    who have one it's up to them to give you an invite.
                </p>
            </div>
        <?php else: ?>
            <form method="post">
                <label>
                    <input name="type" value="<?= $register_type ?>" style="display: none">
                </label>
                <div class="row">
                    <div class="form-group col-md-4">
                        <div class="form-group">
                            <label class="control-label" for="username">Username</label>
                            <input autofocus="" class="form-control" id="username" name="username"
                                   placeholder="Username"
                                   title="" type="text" value="" required="required">
                            <small>Max Length 12 with those character: <code>A-Za-z0-9_</code></small>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="email">Email</label>
                            <input autofocus="" class="form-control" id="email" name="email" placeholder="Email"
                                   title="" type="email" value="" required="required">
                            <small>We only allow those Email:
                                <code><?= app()->config->get('register.email_white_list') ?></code></small>
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password">Password</label>
                            <input autofocus="" class="form-control" id="password" name="password"
                                   placeholder="Password"
                                   title="" type="password" value="" required="required">
                        </div>
                        <div class="form-group">
                            <label class="control-label" for="password_again">Retype Password Again</label>
                            <input autofocus="" class="form-control" id="password_again" name="password_again"
                                   placeholder="Password"
                                   title="" type="password" value="" required="required">
                        </div>


                        <?php if ($register_type == 'invite') : ?>
                            <?php $invite_hash = app()->request->get('invite_hash', '') ?>
                            <div class="form-group">
                                <label class="control-label" for="invite_hash">Invite Code</label>
                                <input autofocus="" class="form-control" id="invite_hash" name="invite_hash"
                                       placeholder="Invite Code"
                                       title="" type="password" value="<?= $invite_hash ?>" required="required">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="accept_tos" value="1" checked> Accept Our TOS and AOP
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <input type="submit" value="Register" class="btn btn-primary">
                    </div>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
<?php $this->end(); ?>
