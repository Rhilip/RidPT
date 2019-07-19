<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/4
 * Time: 20:40
 *
 * @var League\Plates\Template\Template $this
 * @var array $torrents
 * @var \apps\models\Torrent $torrent
 * @var int $count
 * @var int $limit
 */

$time_now = time();
?>

<?php $this->insert('common/helper') ?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents List<?php $this->end();?>

<?php $this->start('container') ?>
<!-- TODO insert container head : For example, Search toolbox..... -->
<div class="row">
    <div class="col-md-12">
        <table class="table" id="torrents_table">
            <thead>
            <tr>
                <th class="text-center" style="width: 20px" title="Type">Type</th>
                <th class="text-center" style="width: 100%;" title="Torrent">Torrents</th>
                <th class="text-center" style="width: 5px" title="Comment"><i class="fas fa-comment-alt fa-fw"></i></th>
                <th class="text-center" style="width: 45px" title="Size">Size</th>
                <th class="text-center" style="width: 80px" title="Date">Date</th>
                <th class="text-center" style="width: 15px" title="Seeders"><i class="fas fa-arrow-up fa-fw color-seeding"></i></th>
                <th class="text-center" style="width: 15px" title="Leechers"><i class="fas fa-arrow-down fa-fw color-leeching"></i></th>
                <th class="text-center" style="width: 15px" title="Completed"><i class="fas fa-check fa-fw color-completed"></i></th>
                <th class="text-center" style="width: 50px" title="Owner"><i class="fas fa-user fa-fw"></i></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($torrents as $torrent): ?>
                <tr data-tid="<?= $torrent->getId() ?>">
                    <td class="text-center" style="margin: 0;padding: 0"><?= ($torrent->getCategory())->getName() ?></td>
                    <td>
                        <div>
                            <div class="pull-left name-left">
                                <div data-item="t-main-info">
                        <span data-item="t-title" data-title="<?= $this->e($torrent->getTitle()) ?>">
                            <a href="/torrent/details?id=<?= $torrent->getId() ?>" target="_blank"><b><?= $torrent->getTitle() ?></b></a>
                        </span>
                                </div>
                                <div data-item="t-sub-info">
                                    <?php $tags = $torrent->getTags(); ?>
                                    <?php if (count($tags) > 0) : ?>
                                        <span data-item="t-tags">
                                <?php foreach ($torrent->getTags() as $tag): ?>
                                    <?php if ($tag['pinned']): // Only show pinned tag in torrent list ?>
                                        <a href="/torrents/search?tags=<?= $tag['tag'] ?>" class="tag label label-outline <?= $tag['class_name'] ?>"><?= $tag['tag'] ?></a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </span>&nbsp;
                                    <?php endif; ?>
                                    <span data-item="subtitle" data-subtitle="<?= $this->e($torrent->getSubtitle()) ?>"><?= $torrent->getSubtitle() ?></span>
                                </div>
                            </div>
                            <div class="pull-right">
                                <div class="text-right">
                                    <!--suppress HtmlUnknownTarget --><a href="/torrent/download?id=<?= $torrent->getId() ?>"><i class="fas fa-download fa-fw"></i></a>
                                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->user->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star fa-fw"></i></a>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center" data-item="t-commit" data-commit="0">0</td> <!-- TODO -->
                    <td class="text-center" data-item="t-size" data-size="<?= $torrent->getTorrentSize() ?>"><?= $this->batch($torrent->getTorrentSize(),'format_bytes_compact') ?></td>
                    <td class="text-center" data-item="t-added-date" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>" data-ttl="<?= $time_now - strtotime($torrent->getAddedAt()) ?>"><nobr><?= str_replace(' ','<br />',$torrent->getAddedAt()) ?></nobr></td>
                    <td class="text-center" data-item="t-seeder" data-seeder="<?= $this->e($torrent->getComplete()) ?>"><?= number_format($torrent->getComplete()) ?></td>
                    <td class="text-center" data-item="t-leecher" data-leecher="<?= $this->e($torrent->getIncomplete()) ?>"><?= number_format($torrent->getIncomplete()) ?></td>
                    <td class="text-center" data-item="t-completed" data-completed="<?= $this->e($torrent->getDownloaded()) ?>"><?= number_format($torrent->getDownloaded()) ?></td>
                    <td class="text-center" data-item="t-uploader" data-uploader="<?= $this->e($torrent->getOwnerId()) ?>"><?= get_torrent_uploader($torrent) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <ul class="pager pager-unset-margin" data-ride="remote_pager" data-rec-total="<?= $count ?>"  data-rec-per-page="<?= $limit ?? 50 ?>"></ul>
        </div>
    </div>
</div>
<?php $this->end();?>
