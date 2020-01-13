<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 18:00
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrent\DetailsForm $details
 */

$torrent = $details->getTorrent();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?><?= $torrent->getTitle() ?><?php $this->end();?>

<?php $this->start('container')?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="row torrent-details-block">
    <div class="col-md-8">
        <div class="panel" id="torrent_descr_panel">
            <article class="panel-body article">
                <header>
                    <dl class="dl-inline">
                        <!-- Team -->
                        <?php if ($torrent->getTeam()): ?>
                        <dt>Team</dt> <dd><?= $torrent->getTeam()['name']; ?></dd>
                        <?php endif; ?>

                        <!-- Quality -->
                        <?php foreach (app()->site->getQualityTableList() as $quality => $title): ?>
                            <?php if (config('torrent_upload.enable_quality_' . $quality) && $torrent->getQuality($quality)) : ?>
                            <dt><?= $title ?></dt> <dd><?= $torrent->getQuality($quality)['name'] ?></dd>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <dt></dt>
                        <dd class="pull-right" id="external_info_label">
                            <a href="#info_douban" data-toggle="collapse" data-parent="#external_info" data-type="douban" data-id="1292052"><span class="label label-success">豆瓣</span></a>
                            <a href="#info_imdb" data-toggle="collapse" data-parent="#external_info" data-type="imdb" data-id="tt0111161"><span class="label label-warning">Imdb</span></a>
                        </dd><!-- FIXME external info label which this torrent have -->
                    </dl>
                    <div class="abstract hide" id="external_info">
                        <div class="row">
                            <div class="collapse col-md-12 info-loading" id="info_douban"></div>
                            <div class="collapse col-md-12 info-loading" id="info_imdb"></div>
                        </div>
                    </div><!-- FIXME external info div block which this site support -->
                </header>
                <section class="content ubbcode-block" id="torrent_descr">
                    <?= $this->batch($torrent->getDescr() ?? '[h4]No description.[/h4]', 'format_ubbcode') ?>
                </section>
            </article>
        </div> <!-- END //*[@id="torrent_descr"] -->
        <div class="panel" id="torrent_commit_panel">
            <div class="panel-heading">
                <b>Last Torrent Commit</b>
                <?php if ($torrent->getComments() > 0): ?>
                    <div class="pull-right"><a href="/torrent/comments?id=<?= $torrent->getId() ?>">[See all comments]</a></div>
                <?php endif; ?>
            </div>
            <div class="panel-body" id="torrent_commit">
                <?= $this->insert('torrent/comments_field', ['torrent' => $torrent, 'comments' => $torrent->getLastCommentsDetails()]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-4" id="torrent_extend_info_panel_group">
        <div class="panel" id="torrent_info_panel">
            <div class="panel-heading"><b>Torrent Information</b></div>
            <div class="panel-body" id="torrent_information">
                <div data-field="category"><b>Type:</b> <?= $torrent->getCategory()['name']  ?></div>
                <div data-field="added_date"><b>Uploaded Date:</b> <?= $torrent->getAddedAt() ?></div>
                <div data-field="size"><b>File size:</b> <?= $this->e($torrent->getTorrentSize(), 'format_bytes') ?></div>
                <div data-field="uploader"><b>Uploader:</b> <?= $this->insert('helper/username', ['user' => $torrent->getOwner(), 'hide' => $torrent->getUplver()]) ?></div>
                <div data-field="peers">
                    <b>Peers:</b>
                    <span class="green"><i class="fas fa-arrow-up fa-fw"></i> <?= $torrent->getComplete() ?></span> /
                    <span class="red"><i class="fas fa-arrow-down fa-fw"></i> <?= $torrent->getIncomplete() ?></span> /
                    <span><i class="fas fa-check fa-fw"></i> <?= $torrent->getDownloaded() ?></span>
                </div>
                <div data-field="info_hash" class="nowrap"><b>Info Hash:</b><kbd><?= $torrent->getInfoHash() ?></kbd></div>
            </div>
        </div>
        <div class="panel" id="torrent_action_panel">
            <div class="panel-heading"><b>Torrent Action</b></div>
            <div class="panel-body" id="torrent_action">
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a href="/torrent/download?id=<?= $torrent->getId() ?>" download><i class="fa fa-download fa-fw"></i>&nbsp;Download Torrent</a>
                </div><!-- Download Torrent -->
                <div class="torrent-action-item">
                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->auth->getCurUser()->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star fa-fw"></i>&nbsp;Add to Favour</a>
                </div><!-- Add to Favour -->
                <div class="torrent-action-item">
                    <a class="torrent-myrss" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="fas fa-rss fa-fw"></i>&nbsp;Add to RSS Basket</a>
                </div><!-- TODO Add to RSS Basket -->
                <hr>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-edit" href="/torrent/edit?id=<?= $torrent->getId() ?>"><i class="fas fa-edit fa-fw"></i>&nbsp;Edit/Remove this Torrent</a>
                </div><!-- TODO Edit/Remove this Torrent -->
                <div class="torrent-action-item">
                    <a class="torrent-subtitles" href="/subtitles/search?tid=<?= $torrent->getId() ?>"><i class="fas fa-closed-captioning fa-fw"></i>&nbsp;Add/View Torrent's Subtitles</a>
                </div>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-report" href="/report?type=torrent&id=<?= $torrent->getId() ?>"><i class="fa fa-bug fa-fw"></i>&nbsp;Report this Torrent</a>
                </div><!-- TODO Report this Torrent -->
                <hr>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-files" href="javascript:"  data-tid="<?= $torrent->getId() ?>"><i class="fas fa-file fa-fw"></i>&nbsp;View Torrent's Files</a>
                </div><!-- View Torrent's Files -->
                <?php if ($torrent->hasNfo()): // TODO add global config key of NFO?>
                    <div class="torrent-action-item">
                        <a class="torrent-nfo" href="javascript:"  data-tid="<?= $torrent->getId() ?>"><i class="fas fa-info fa-fw"></i>&nbsp;View Torrent's Nfo file</a>
                    </div><!-- View Torrent's Nfo -->
                <?php endif;?>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-structure" href="/torrent/structure?id=<?= $torrent->getId() ?>"><i class="fas fa-folder-open fa-fw"></i>&nbsp;View Torrent's Structure</a>
                </div><!-- View Torrent's Structure -->
                <hr>
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-peers" href="javascript:"  data-tid="<?= $torrent->getId() ?>"><i class="fas fa-user-friends fa-fw"></i>&nbsp;See Current Peers</a>
                </div><!-- View Torrent's Files -->
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a class="torrent-snatch" href="/torrent/snatch?id=<?= $torrent->getId() ?>"><i class="fas fa-bars fa-fw"></i>&nbsp;See Snatched Record</a>
                </div><!-- View Torrent's Structure -->
            </div>
        </div>
        <div class="panel" id="torrent_tags_panel">
            <div class="panel-heading"><b>Torrent Tags</b></div>
            <div class="panel-body" id="torrent_tags">
                <?php $tags = $torrent->getTags(); ?>
                <?php if (count($tags) > 0) : ?>
                    <?php $pinned_tags = $torrent->getPinnedTags(); ?>
                    <?php foreach ($tags as $tag): ?>
                        <a href="/torrents/search?tags=<?= $tag ?>" class="label label-outline <?= array_key_exists($tag, $pinned_tags) ? $pinned_tags[$tag] : '' ?>"><?= $tag ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted">No tags for this torrent</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
