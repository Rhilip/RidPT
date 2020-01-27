<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/6
 * Time: 16:33
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Entity\User\User $user
 */

use App\Entity\User\UserStatus;

?>

<?= $this->layout('layout/base') ?>

<?php $this->start('container') ?>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <div class="text-center">
            <h2><?= $user->getUsername() ?>'s Invite System</h2>
            <?php if (isset($msg)): ?>
            <small class="text-red"><?= $msg ?></small>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="panel-heading">Invitee Status</div>
            <div class="panel-body">
                <?php if ($user->getInvitees()): ?>
                <table class="table table-hover table-striped">
                    <thead>
                    <tr>
                        <td class="text-center">Username</td>
                        <td class="text-center">Email</td>
                        <td class="text-right">Uploaded</td>
                        <td class="text-right">Downloaded</td>
                        <td class="text-center">Ratio</td>
                        <td class="text-center">Status</td>
                        <?php if (app()->auth->getCurUser()->isPrivilege('invite_manual_confirm')): ?>
                        <td class="text-center">Confirm</td>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($user->getInvitees() as $invitee): ?>
                    <tr>
                        <td class="text-center"><?= $invitee['username'] ?></td>
                        <td class="text-center"><?= $invitee['email'] ?></td>
                        <td class="text-right"><?= $this->batch($invitee['uploaded'], 'format_bytes') ?></td>
                        <td class="text-right"><?= $this->batch($invitee['downloaded'], 'format_bytes') ?></td>
                        <td class="text-center"><?= number_format($invitee['uploaded']/($invitee['downloaded'] + 1), 3) ?></td>
                        <td class="text-center"><?= $invitee['status'] ?></td>
                        <?php if (app()->auth->getCurUser()->isPrivilege('invite_manual_confirm')): ?>
                        <td class="text-center">
                            <?php if ($invitee['status'] == UserStatus::PENDING): ?>
                            <a class="btn btn-info btn-sm" href="?action=confirm&uid=<?= $this->e($invitee['id']) ?>" onclick="return confirm('Really?')">Confirm</a>
                            <?php endif ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
                <?php else: ?>
                    <span class="text-muted">No user in Our site is invite by you.</span>
                <?php endif; ?>
            </div>
        </div> <!-- User's Invitee list -->

        <div class="panel">
            <div class="panel-heading">Pending Invite</div>
            <div class="panel-body">
                <?php if ($user->getPendingInvites()): ?>
                <?php $can_recyle = $user->getId() === app()->auth->getCurUser()->getId() ?
                        app()->auth->getCurUser()->isPrivilege('invite_recycle_self_pending') :
                        app()->auth->getCurUser()->isPrivilege('invite_recycle_other_pending'); ?>
                    <table class="table table-hover table-striped">
                        <thead>
                        <tr>
                            <td class="text-center">Username</td>
                            <td class="text-center">Invite Hash</td>
                            <td class="text-center">Create At</td>
                            <td class="text-center">Expire At</td>
                            <?php if ($can_recyle): ?>
                            <td class="text-center">Recycle</td>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($user->getPendingInvites() as $pendingInvite): ?>
                        <?php $invite_link = (app()->request->isSecure() ? 'https://' : 'http://') . config('base.site_url') . '/auth/register?type=invite&invite_hash=' . $pendingInvite['hash']; ?>
                            <tr>
                                <td class="text-center"><?= $pendingInvite['username'] ?></td>
                                <td class="text-center"><a href="<?= $invite_link ?>" target="_blank" data-toggle="tooltip" data-placement="right" title="Right mouse button to copy"><?= $pendingInvite['hash'] ?></a></td>
                                <td class="text-center"><nobr><?= $pendingInvite['create_at'] ?></nobr></td>
                                <td class="text-center"><nobr><?= $pendingInvite['expire_at'] ?></nobr></td>
                                <?php if ($can_recyle): ?>
                                    <td class="text-center"><a class="btn btn-warning btn-sm" href="?action=recycle&invite_id=<?= $this->e($pendingInvite['id']) ?>" onclick="return confirm('Really?')">Recycle</a></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <span class="text-muted">No Pending Invite.</span>
                <?php endif; ?>
            </div>
        </div> <!-- User's Pending Invite -->

        <?php if ($user->getId() === app()->auth->getCurUser()->getId()): // Same User, use $user as quick call?>
        <?php $can_invite = config('base.enable_invite_system') && ($user->getInvites() + $user->getTempInvitesSum() > 0); ?>
        <div class="panel">
            <div class="panel-heading"><span class="text-red">Invite Warning!!!</span></div>
            <div class="panel-body">
                <ul>
                    <li>Only invite people you trust and who will maintain their ratio.</li>
                    <li>Do not use your invites to create another account (dupe account), this WILL lead to both accounts being disabled!</li>
                    <li>Selling invites WILL get you banned without warning.</li>
                    <li>Do not re-invite an invitee that has already been banned, this WILL lead to all accounts being disabled.</li>
                    <li><b>We have a invite interval, So please wait <?= config('invite.interval') ?> seconds to send another invite.</b></li>
                </ul>
                <hr />
                <div class="text-center">
                    At now, You have <span id="user_permanent_invite"<?= $user->getInvites() > 0 ? ' class="text-red"' : '' ?>><?= $user->getInvites() ?></span> permanent invites.
                    <?php if ($user->getTempInvitesSum() > 0): ?>
                        And <span id="user_temporarily_invite"<?= $user->getTempInvitesSum() > 0 ? ' class="text-red"' : '' ?>><?= $user->getTempInvitesSum() ?></span> temporarily invites, which details are list below:
                        <div id="user_temp_invite_details" class="text-center row">
                            <div class="col-md-6 col-md-offset-3">
                                <table class="table table-striped table-hover">
                                    <thead>
                                    <tr>
                                        <td>Id</td>
                                        <td><nobr>Total Invite Count</nobr></td>
                                        <td><nobr>Left Invite Count</nobr></td>
                                        <td><nobr>Expired at</nobr></td>
                                        <td>Use</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $i = 1; ?>
                                    <?php foreach ($user->getTempInviteDetails() as $tempInviteDetail): ?>
                                        <?php $left = $tempInviteDetail['total'] - $tempInviteDetail['used']; ?>
                                        <tr <?= strtotime($tempInviteDetail['expire_at']) - time() < 86400 ? ' class="warning"' : ''; ?>>
                                            <td><?= $i ?></td>
                                            <td><?= $tempInviteDetail['total'] ?></td>
                                            <td <?= $left < 2 ? ' class="text-danger"' : '' ?>><?= $left ?></td>
                                            <td><nobr><?= $tempInviteDetail['expire_at'] ?></nobr></td>
                                            <td><button class="btn btn-primary btn-sm invite-btn" type="button" data-type="temporarily" data-id="<?= $i ?>" data-temp-invite-id="<?= $tempInviteDetail['id'] ?>"<?= $left == 0 ? ' disabled': '' ?>>Use it!</button></td>
                                        </tr>
                                        <?php $i++; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($user->getInvites() > 0): ?>
                        <button class="btn btn-primary invite-btn" type="button" data-type="permanent">Use Permanent Invite!</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($can_invite): ?>
        <div class="panel" id="invite_form" style="display: none">
            <div class="panel-heading">Invite Form <span id="invite_type" class="text-info"></span></div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" id="invite_create_form">
                    <label><input name="invite_type" value="permanent" style="display: none"></label>
                    <label><input name="temp_id" value="0" style="display: none"></label>
                    <div class="form-group">
                        <label for="username" class="col-sm-2">Username</label>
                        <div class="col-md-6 col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fas fa-user-alt fa-fw"></span></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="help-block">
                                <ul>
                                    <li>The invitee can't change.</li>
                                    <li>Max Length 12 with those character: <code>A-Za-z0-9_</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-2">Email</label>
                        <div class="col-md-6 col-sm-10">
                            <div class="input-group">
                                <span class="input-group-addon"><span class="fas fa-envelope fa-fw"></span></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="help-block">We only allow those Email: <code><?= config('register.email_white_list') ?></code></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-2">Message</label>
                        <div class="col-md-6 col-sm-10">
                            <label>
<textarea name="message" class="form-control" rows="8" cols="120">
Hi,

I am inviting you to join RidPT which is a private community that have the finest and most abundant stuff. If you are interested in joining us please read over the rules and confirm the invite. Finally, please make sure you keep a nice ratio and only upload content that follows rules of the site.

Welcome aboard! :)
Best Regards,
Admin
</textarea>
                            </label>
                        </div>
                    </div>
                    <?= $this->insert('layout/captcha', ['inline' => true]) ?>
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary">Send~</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<?php $this->stop() ?>

