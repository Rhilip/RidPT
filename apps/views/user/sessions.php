<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 17:46
 *
 * @var array $sessions
 */
?>

<?= $this->layout('user/setting_layout') ?>

<?php $this->start('panel') ?>
<h1>Sessions</h1>
This is a list of devices that have logged into your account. Revoke any sessions that you do not recognize.
<br>
<table class="table table-hover table-striped">
    <thead>
    <tr>
        <td class="text-center">Login At</td>
        <td class="text-center">Login IP</td>
        <td class="text-center">User Agent</td>
        <td class="text-center">Last access at</td>
        <td class="text-center">Action</td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($sessions as $s): ?>
        <tr>
            <td class="text-center"><?= $s['login_at'] ?></td>
            <td class="text-center"><?= inet_ntop($s['login_ip']) ?></td>
            <td class="text-left"><?= $s['user_agent'] ?></td>
            <td class="text-center" data-timestamp="<?= strtotime($s['last_access_at']) ?>"><?= $s['last_access_at'] ?></td>
            <td class="text-center">
                <?php if ($s['sid'] == app()->user->getSessionId()): ?>
                    Current
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="delsession"/>
                        <input type="hidden" name="session" value="<?= $s['sid'] ?>"/>
                        <button class="btn btn-default" type="submit"
                                onclick="return confirm('Are you sure you want to delete this session?');">
                            <i class="far fa-trash-alt"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php $this->stop() ?>
