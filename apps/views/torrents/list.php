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
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents List<?php $this->end();?>

<?php $this->start('container')?>
<table class="layui-table">
    <thead>
    <tr>
        <th class="text-center" style="width: 70px">Category</th>
        <th>Torrent</th>
        <th class="text-center" style="width: 70px">Link</th>
        <th class="text-center" style="width: 100px">Size</th>
        <th class="text-center" style="width: 140px">Date</th>
        <th class="text-center" style="width: 30px"><i class="fas fa-arrow-up" title="Seeders"></th>
        <th class="text-center" style="width: 30px"><i class="fas fa-arrow-down" title="Leechers"></i></th>
        <th class="text-center" style="width: 30px"><i class="fas fa-check" title="Completed"></th>
        <th class="text-center" style="width: 70px"><i class="fas fa-user" title="Owner"></i></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($torrents as $torrent): ?>
    <tr>
        <td class="text-center"><?= ($torrent->getCategory())->getName() ?></td>
        <td>
            <!--suppress HtmlUnknownTarget -->
            <a href="/torrents/details?id=<?= $torrent->getId() ?>" target="_blank"><?= $torrent->getTitle() ?></a>
        </td>
        <td class="text-center">
            <!--suppress HtmlUnknownTarget -->
            <a href="/torrents/download?id=<?= $torrent->getId() ?>"><i class="fas fa-download"></i></a>
        </td>
        <td class="text-center" data-bytes-size="<?= $torrent->getTorrentSize() ?>"><?= $this->e($torrent->getTorrentSize(),'format_bytes') ?></td>
        <td class="text-center" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>"><?= $torrent->getAddedAt() ?></td>
        <td class="text-center"><?= $torrent->getComplete() ?></td>
        <td class="text-center"><?= $torrent->getIncomplete() ?></td>
        <td class="text-center"><?= $torrent->getDownloaded() ?></td>
        <td class="text-center">
            <?php if ($torrent->getUplver() == 'yes' and app()->user->getClass(true) < app()->config->get('authority.see_anonymous_uploader')): ?>
                <i>Anonymous</i>
            <?php else: ?>
                <!--suppress HtmlUnknownTarget -->
                <a class="text-default" href="/user/panel?id=<?= $torrent->getOwnerId() ?>" data-toggle="tooltip" title="User"><?= $torrent->getOwner()->getUsername() ?></a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php $this->end();?>
