<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 17:10
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrent\SnatchForm $snatch
 */

$timenow = time();
$torrent = $snatch->getTorrent();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents Snatched Details<?php $this->end();?>

<?php $this->start('container')?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel" id="torrent_snatched_details_panel">
            <div class="panel-heading"><b>Torrent Snatched Details</b></div>
            <div class="panel-body" id="torrent_snatched_details_body">
                <div id="torrent_snatched_details">
                    <?php if ($snatch->getTotal()): ?>
                    <table class="table table-hover table-condensed">
                        <thead>
                        <tr>
                            <th>Username</th>
                            <th>Ip Address</th> <!-- FIXME check privilege -->
                            <th>IP on finished</th>
                            <th>Uploaded/Downloaded</th>
                            <th>Ratio</th>
                            <th>Se. Time</th>
                            <th>Le. Time</th>
                            <th>Finished?</th>
                            <th>Completed At</th>
                            <th>Last Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($snatch->getPagerData() as $snatchDetail): ?>
                        <tr>
                            <td><?= $this->insert('helper/username', ['user' => app()->site->getUser($snatchDetail['user_id'])]) ?></td> <!-- TODO hide username when user has Strong Privacy  -->
                            <td><?= inet_ntop($snatchDetail['ip']) ?></td>
                            <td><?= $snatchDetail['finish_ip'] ? inet_ntop($snatchDetail['finish_ip']) : '?' ?></td>
                            <td>
                                <?= $this->e($snatchDetail['this_uploaded'], 'format_bytes') ?>@<?= $this->e($snatchDetail['this_uploaded'] > 0 ? ($snatchDetail['this_uploaded'] / ($snatchDetail['seed_time'] + $snatchDetail['leech_time'])) : 0, 'format_bytes') ?>/s <br>
                                <?= $this->e($snatchDetail['this_download'], 'format_bytes') ?>@<?= $this->e($snatchDetail['this_download'] > 0 ? ($snatchDetail['this_download'] / $snatchDetail['leech_time']) : 0, 'format_bytes') ?>/s <br>
                            </td>
                            <td>
                                <?php if ($snatchDetail['this_download'] > 0): ?>
                                    <?= number_format($snatchDetail['this_uploaded'] / $snatchDetail['this_download'], 3) ?>
                                <?php elseif ($snatchDetail['this_uploaded'] > 0): ?>
                                    Inf.
                                <?php else: ?>
                                    ---
                                <?php endif; ?>
                            </td>
                            <td><?= $this->e($snatchDetail['seed_time'], 'sec2hms') ?></td>
                            <td><?= $this->e($snatchDetail['leech_time'], 'sec2hms') ?></td>
                            <td><?= $snatchDetail['finished'] ?></td>
                            <td><?= $snatchDetail['finish_at'] ?></td>
                            <td><?= $snatchDetail['last_action_at'] ?></td>
                        </tr>


                        <?php endforeach; ?>
                        </tbody>
                    </table>
                        <div class="text-center">
                            <ul class="pager pager-unset-margin" data-ride="remote_pager" data-rec-total="<?= $snatch->getTotal() ?>" data-rec-per-page="<?= $snatch->getLimit() ?>"></ul>
                        </div>
                    <?php else: ?>
                    No Snatched Records exist.
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
