<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 18:00
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\Torrent $torrent
 */

?>

<?php $this->insert('common/helper') ?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?><?= $torrent->getTitle() ?><?php $this->end();?>

<?php $this->start('container')?>

<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="layui-row layui-col-space15">
    <div class="layui-col-md8">
        <div class="layui-card" id="torrent-descr-card">
            <div class="layui-card-header"><b>Torrent Description</b></div>
            <div class="layui-card-body ubb" id="torrent-descr">
                <div class="ubbcode-block">
                <?= $torrent->getDescr() ? $this->batch($torrent->getDescr(),'format_ubbcode') : '[h4]No description.[/h4]' ?>
                </div>
            </div>
        </div>
        <div class="layui-card" id="torrent-commit-card">
            <div class="layui-card-header"><b>Torrent Commit</b></div>
            <div class="layui-card-body" id="torrent-commit">
                // TODO
            </div>
        </div>
    </div>
    <div class="layui-col-md4">
        <div class="layui-card" id="torrent-action-card">
            <div class="layui-card-header"><b>Torrent Action</b></div>
            <div class="layui-card-body">
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a href="/torrent/download?id=<?= $torrent->getId() ?>"><i class="fa fa-download fa-fw"></i>&nbsp;Download Torrent</a>
                </div><!-- Download Torrent -->
                <div class="torrent-action-item">
                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->user->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star fa-fw"></i>&nbsp;Add to Favour</a>
                </div><!-- Add to Favour -->
                <div class="torrent-action-item">
                    <a class="torrent-myrss" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="fas fa-rss fa-fw"></i>&nbsp;Add to RSS Basket</a>
                </div><!-- TODO Add to RSS Basket -->
                <hr>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-edit" href="/torrent/edit?id=<?= $torrent->getId() ?>"><i class="fas fa-edit fa-fw"></i>&nbsp;Edit/Remove this Torrent</a>
                </div><!-- TODO Edit/Remove this Torrent -->
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-report" href="/report?type=torrent&id=<?= $torrent->getId() ?>"><i class="fa fa-bug fa-fw"></i>&nbsp;Report this Torrent</a>
                </div><!-- TODO Report this Torrent -->
                <hr>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-files" href="javascript:"  data-tid="<?= $torrent->getId() ?>"><i class="fas fa-file fa-fw"></i>&nbsp;View Torrent's Files</a>
                </div><!-- View Torrent's Files -->
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-structure" href="javascript:"  data-tid="<?= $torrent->getId() ?>"><i class="fas fa-folder-open fa-fw"></i>&nbsp;View Torrent's Structure</a>
                </div><!-- TODO View TorrentController's Structure -->
            </div>
        </div>
        <div class="layui-card" id="torrent-info-card">
            <div class="layui-card-header"><b>Torrent Information</b></div>
            <div class="layui-card-body">
                <div data-field="added_date" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>"><b>Uploaded Date:</b> <?= $torrent->getAddedAt() ?></div>
                <div data-field="size" data-filesize="<?= $torrent->getTorrentSize() ?>"><b>File size:</b> <?= $this->e($torrent->getTorrentSize(),'format_bytes') ?></div>
                <div data-field="uploader" data-owner-id="<?= get_torrent_uploader_id($torrent) ?>"><b>Uploader:</b> <?= get_torrent_uploader($torrent) ?></div>
                <div data-field="peers" data-seeders="<?= $torrent->getComplete() ?>" data-leechers="<?= $torrent->getComplete() ?>" data-completed="<?= $torrent->getDownloaded() ?>">
                    <b>Peers:</b> <span style="color: green;"><i class="fas fa-arrow-up fa-fw"></i> <?= $torrent->getComplete() ?></span> / <span style="color: red;"><i class="fas fa-arrow-down fa-fw"></i> <?= $torrent->getIncomplete() ?></span> / <span><i class="fas fa-check fa-fw"></i> <?= $torrent->getDownloaded() ?></span>
                </div>
                <div data-field="info_hash" data-infohash="<?= $torrent->getInfoHash() ?>"><b>Info Hash:</b> <kbd><?= $torrent->getInfoHash() ?></kbd></div>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
