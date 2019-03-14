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
 */

include 'helper.php';
$time_now = time();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents List<?php $this->end();?>

<?php $this->start('container')?>
<table class="layui-table">
    <thead>
    <tr>
        <th class="text-center" style="width: 20px" title="Category">Category</th>
        <th class="text-center" title="Torrent">Torrents</th>
        <th class="text-center" style="width: 5px" title="Comment"><i class="fas fa-comment-alt"></i></th>
        <th class="text-center" style="width: 50px" title="Size">Size</th>
        <th class="text-center" style="width: 100px" title="Date">Date</th>
        <th class="text-center" style="width: 15px" title="Seeders"><i class="fas fa-arrow-up"></i></th>
        <th class="text-center" style="width: 15px" title="Leechers"><i class="fas fa-arrow-down"></i></th>
        <th class="text-center" style="width: 15px" title="Completed"><i class="fas fa-check"></i></th>
        <th class="text-center" style="width: 50px" title="Owner"><i class="fas fa-user"></i></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($torrents as $torrent): ?>
    <tr data-tid="<?= $torrent->getId() ?>">
        <td class="text-center"><?= ($torrent->getCategory())->getName() ?></td>
        <td>
            <div class="pull-left">
                <!--suppress HtmlUnknownTarget -->
                <a href="/torrents/details?id=<?= $torrent->getId() ?>" target="_blank"><?= $torrent->getTitle() ?></a>
            </div>
            <div class="pull-right">
                <div class="text-center" style="width: 5px">
                    <!--suppress HtmlUnknownTarget -->
                    <a href="/torrents/download?id=<?= $torrent->getId() ?>"><i class="fas fa-download"></i></a>
                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->user->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star"></i></a>
                </div>
            </div>
        </td>
        <td class="text-center">0</td> <!-- TODO -->
        <td class="text-center" data-bytes-size="<?= $torrent->getTorrentSize() ?>"><?= $this->e($torrent->getTorrentSize(),'format_bytes') ?></td>
        <td class="text-center" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>" data-ttl="<?= $time_now - strtotime($torrent->getAddedAt()) ?>"><?= $torrent->getAddedAt() ?></td>
        <td class="text-center"><?= number_format($torrent->getComplete()) ?></td>
        <td class="text-center"><?= number_format($torrent->getIncomplete()) ?></td>
        <td class="text-center"><?= number_format($torrent->getDownloaded()) ?></td>
        <td class="text-center"><?= get_torrent_uploader($torrent) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php $this->end();?>
