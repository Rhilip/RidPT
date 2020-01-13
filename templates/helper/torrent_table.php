<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/4
 * Time: 20:40
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrents\SearchForm $search
 */

$time_now = time();
?>

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
            <?php foreach ($search->getPagerData() as $torrent): /** @var \App\Entity\Torrent $torrent */ ?>
                <tr data-tid="<?= $torrent->getId() ?>">
                    <td class="text-center" data-item="category" style="margin: 0;padding: 0">
                        <?php $cat = $torrent->getCategory(); ?>
                        <?php if ($cat['image']): // Show Category's Image as <img> tag with classname?>
                            <img src="<?= $cat['image'] ?>" class="category <?= $cat['class_name'] ?>"  alt="<?= $cat['name'] ?>">
                        <?php elseif ($cat['class_name']):  // Show Category's Image as <div> tag with classname?>
                            <div class="category <?= $cat['class_name'] ?>"></div>
                        <?php else: // Show Category's Name if image not set?>
                            <?= $cat['name'] ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div>
                            <div class="name-left">
                                <div data-item="t-main-info">
                                    <span data-item="t-title" data-title="<?= $this->e($torrent->getTitle()) ?>">
                                        <a href="/torrent/details?id=<?= $torrent->getId() ?>" target="_blank"><b><?= $torrent->getTitle() ?></b></a>
                                    </span>
                                </div>
                                <div data-item="t-extra-info">
                                    <?php $tags = $torrent->getPinnedTags(); ?>
                                    <?php if (count($tags) > 0) : ?>
                                        <span data-item="t-tags">
                                            <?php foreach ($tags as $tag_name => $class_name): ?>
                                                <a href="/torrents/search?tags=<?= $tag_name ?>" class="tag label label-outline <?= $class_name ?>"><?= $tag_name ?></a>
                                            <?php endforeach; ?>
                                        </span>&nbsp;
                                    <?php endif; ?>
                                    <?php if ($torrent->getSubtitle()): ?>
                                        <span data-item="subtitle" data-subtitle="<?= $this->e($torrent->getSubtitle()) ?>"><?= $torrent->getSubtitle() ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="name-right">
                                <div class="text-right">
                                    <!--suppress HtmlUnknownTarget --><a href="/torrent/download?id=<?= $torrent->getId() ?>" download><i class="fas fa-download fa-fw"></i></a>
                                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->auth->getCurUser()->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star fa-fw"></i></a>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center" data-item="t-comments" data-comments="<?= $torrent->getComments() ?>"><?= $torrent->getComments() ?></td>
                    <td class="text-center" data-item="t-size" data-size="<?= $torrent->getTorrentSize() ?>"><?= $this->batch($torrent->getTorrentSize(), 'format_bytes_compact') ?></td>
                    <td class="text-center" data-item="t-added-date"><time class="nowrap" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>" data-ttl="<?= $time_now - strtotime($torrent->getAddedAt()) ?>"><?= str_replace(' ', '<br />', $torrent->getAddedAt()) ?></time></td>
                    <td class="text-center" data-item="t-seeder" data-seeder="<?= $this->e($torrent->getComplete()) ?>"><?= number_format($torrent->getComplete()) ?></td>
                    <td class="text-center" data-item="t-leecher" data-leecher="<?= $this->e($torrent->getIncomplete()) ?>"><?= number_format($torrent->getIncomplete()) ?></td>
                    <td class="text-center" data-item="t-completed" data-completed="<?= $this->e($torrent->getDownloaded()) ?>">
                        <?php if ($torrent->getDownloaded() > 0): ?><a href="/torrent/snatch?id=<?= $torrent->getId() ?>"><?php endif; ?>
                            <?= number_format($torrent->getDownloaded()) ?>
                            <?php if ($torrent->getDownloaded() > 0): ?></a><?php endif; ?>
                    </td>
                    <td class="text-center" data-item="t-uploader" data-uploader="<?= $this->e($torrent->getUplver() ? 0 : $torrent->getOwnerId()) ?>"><?= $this->insert('helper/username', ['user' => $torrent->getOwner(), 'hide' => $torrent->getUplver()]) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <ul class="pager pager-unset-margin" data-ride="remote_pager" data-rec-total="<?= $search->getTotal() ?>" data-rec-per-page="<?= $search->getLimit() ?>"></ul>
        </div>
    </div>
</div>
