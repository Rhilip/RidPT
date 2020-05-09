<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 17:46
 *
 * @var \App\Models\Form\User\SessionsListForm $session_list
 */
?>

<?= $this->layout('user/setting_layout') ?>

<?php $this->start('panel') ?>
<div class="row">
    <div class="col-md-12">
        <h1>Sessions</h1>
        This is a list of devices that have logged into your account. Revoke any sessions that you do not recognize.
    </div>
    <div class="col-md-12">
        <table class="table table-hover table-striped">
            <thead>
            <tr>
                <td class="text-center">Id</td>
                <td class="text-center">Login At</td>
                <td class="text-center">Login IP</td>
                <td class="text-center">Revoke</td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($session_list->getPagerData() as $s): ?>
                <tr<?php if ($s['expired'] == 0):?> class="warning" data-toggle="tooltip" data-placement="bottom" title="This session will expired automatically."<?php endif;?>>
                    <td><?= $s['id'] ?></td>
                    <td class="text-center"><time class="nowrap"><?= $s['login_at'] ?></time></td>
                    <td class="text-center"><?= inet_ntop($s['login_ip']) ?></td>
                    <td class="text-center">
                        <?php if ($s['session'] == \Rid\Helpers\ContainerHelper::getContainer()->get('auth')->getCurUserJIT()): ?>
                            Current
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="action" value="revoke"/>
                                <input type="hidden" name="session" value="<?= $s['session'] ?>"/>
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

        <div class="text-center">
            <ul class="pager pager-unset-margin" data-ride="remote_pager" data-rec-total="<?= $session_list->getTotal() ?>" data-rec-per-page="<?= $session_list->getLimit() ?>"></ul>
        </div>
    </div>
</div>

<?php $this->stop() ?>
