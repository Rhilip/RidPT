<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 17:13
 *
 * @var \App\Models\Form\User\UserDetailsForm $details
 */

$user = $details->getUser();
?>

<?=  $this->layout('layout/base') ?>

<?php $this->start('container') ?>
<div class="row">

    <div class="col-md-3">
        <div class="thumbnail">
            <img src="<?= $user->getAvatar(['s'=> 250]) ?>" width="250" height="250" alt="" class="img-responsive img-rounded">
            <div class="caption">
                <h3><?= $user->getUsername() ?></h3>
                <h5 class="label label-primary"><?= $user->getClass() ?></h5>
                <h5><?= $user->getEmail() ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Information</h3>
            </div>
            <div class="panel-body">
                <dl class="dl-horizontal text-overflow">
                    <dt>Registration Time</dt><dd><?= $details->getCreateAt() ?></dd>
                    <dt>Last Login Time</dt><dd><?= $details->getLastLoginAt() ?></dd>
                    <dt>Last Access Time</dt><dd><?= $details->getLastAccessAt() ?></dd>
                    <?php if ($details->getLastUploadAt() > 0 or $details->getLastDownloadAt() or $details->getLastConnectAt() > 0): ?>
                        <dt>Last Tracker Time</dt>
                        <dd>
                            <?php if ($details->getLastUploadAt() > 0): ?> Upload: <?= $details->getLastUploadAt() ?> <br><?php endif;?>
                            <?php if ($details->getLastDownloadAt() > 0): ?> Download: <?= $details->getLastDownloadAt() ?> <br><?php endif;?>
                            <?php if ($details->getLastConnectAt() > 0): ?> Connect: <?= $details->getLastConnectAt() ?> <br><?php endif;?>
                        </dd>
                    <?php endif; ?>
                </dl>
                <hr>
                <dl class="dl-horizontal text-overflow">
                    <?php if ($details->getRegisterIp()): ?><dt>Registration Ip</dt><dd><?= $details->getRegisterIp() ?></dd><?php endif;?>
                    <?php if ($details->getLastLoginIp()): ?><dt>Last Login Ip</dt><dd><?= $details->getLastLoginIp() ?></dd><?php endif;?>
                    <?php if ($details->getLastAccessIp()): ?><dt>Last Access Ip</dt><dd><?= $details->getLastAccessIp() ?></dd><?php endif;?>
                    <?php if ($details->getLastTrackerIp()): ?><dt>Last Tracker Ip</dt><dd><?= $details->getLastTrackerIp() ?></dd><?php endif;?>
                </dl>
                <hr>
                <dl class="dl-horizontal text-overflow">
                    <dt>BT Transport</dt>
                    <dd>
                        Ratio : <?= is_string($user->getRatio()) ? $user->getRatio() : round($user->getRatio(), 3) ?>
                        ( uploaded : <?= $this->e(app()->auth->getCurUser()->getUploaded(), 'format_bytes') ?> and downloaded : <?= $this->e(app()->auth->getCurUser()->getDownloaded(), 'format_bytes') ?>) <br>
                        Real Ratio : <?= is_string($user->getRealRatio()) ? $user->getRealRatio() : round($user->getRealRatio(), 3) ?>
                        ( uploaded : <?= $this->e(app()->auth->getCurUser()->getRealUploaded(), 'format_bytes') ?> and downloaded : <?= $this->e(app()->auth->getCurUser()->getRealDownloaded(), 'format_bytes') ?>) <br>
                    </dd>
                    <dt>BT Time</dt>
                    <dd>Ratio : <?= round($user->getTimeRatio(), 2) ?>  ( Seeding Time: <?= $user->getSeedtime() ?> , Leeching Time: <?= $user->getLeechTime() ?>)</dd>
                </dl>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">BT Activities</h3>
            </div>
            <div class="panel-body">
                TODO
            </div>
        </div>
    </div>
</div>
<?php $this->stop() ?>
