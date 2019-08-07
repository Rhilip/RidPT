<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/22
 * Time: 18:00
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\form\Torrent\DetailsForm $details
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
            <div class="panel-body">
                <div class="ubbcode-block" id="torrent_descr">
                    <?= $this->batch($torrent->getDescr() ?? '[h4]No description.[/h4]','format_ubbcode') ?>
                </div>
            </div>
        </div> <!-- END //*[@id="torrent_descr"] -->
        <div class="panel" id="torrent_commit_panel">
            <div class="panel-heading">
                <b>Torrent Commit</b>
                <?php if ($torrent->getComments() !== count($torrent->getLastCommentsDetails())): ?>
                    <div class="pull-right"><a href="/torrent/comments?tid=<?= $torrent->getId() ?>">[See more comments]</a></div>
                <?php endif; ?>
            </div>
            <div class="panel-body" id="torrent_commit">
                <div class="comments">
                    <?php if ($torrent->getComments()): ?>
                        <section class="comments-list">
                            <?php foreach ($torrent->getLastCommentsDetails() as $commit): ?>
                            <?php
                                $commit_user = app()->site->getUser($commit['owner_id']);

                                // The details of commentator should be hide or not ?
                                $commentator_hide_flag = false;
                                if ($torrent->getUplver() &&  // The torrent is uplver
                                    $commit['owner_id'] == $torrent->getOwnerId() &&  // Commentator is the uploader for this torrent
                                    !app()->site->getCurUser()->isPrivilege('see_anonymous_uploader')  // CurUser can't see uploader detail
                                ) $commentator_hide_flag = true;
                            ?>
                            <div id="commit_<?= $commit['id'] ?>" class="comment">
                                <div class="avatar">
                                    <?php if ($commentator_hide_flag): ?>
                                        <i class="icon-user icon-3x"></i>
                                    <?php else: ?>
                                        <img src="<?= $commit_user->getAvatar() ?>" alt="">
                                    <?php endif; ?>
                                </div>
                                <div class="content">
                                    <div class="pull-right text-muted">
                                        <a href="#commit_<?= $commit['id'] ?>">#<?= $commit['id'] ?></a> -
                                        <?php if ($commit['create_at'] != $commit['edit_at']): ?>
                                            <span>Edited at <?= $commit['edit_at'] ?> </span>
                                        <?php else: ?>
                                            <span>Created at <?= $commit['create_at'] ?> </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-username">
                                        <?= $this->insert('helper/username', ['user' => $commit_user, 'torrent' => $torrent, 'user_badge' => true]) ?>
                                    </div>
                                    <div class="text ubbcode-block"><?= $this->batch($commit['text'], 'format_ubbcode') ?></div>
                                    <div class="actions pull-right"> <!-- TODO -->
                                        <a href="##">Report</a>
                                        <a href="##">Reply</a>
                                        <a href="##">Edit</a>
                                        <a href="##">Delete</a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>
                    <footer>
                        <div class="reply-form" id="commentReplyForm1">
                            <div class="avatar"><img src="<?= app()->site->getCurUser()->getAvatar() ?>" alt=""></div>
                            <form  class="form" method="post" action="/torrent/commit?id=<?= $torrent->getId() ?>"> <!-- FIXME commit point -->
                                <div class="form-group">
                                    <textarea class="form-control new-comment-text" rows="3" placeholder="Quick Commit Here" data-autoresize></textarea>
                                </div>
                                <div class="form-group comment-user">
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div id="quote_help_block"></div>
                                        </div>
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-block btn-primary"><i class="far fa-paper-plane"></i>&nbsp;&nbsp;Send</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4" id="torrent_extend_info_panel_group">
        <div class="panel" id="torrent_action_panel">
            <div class="panel-heading"><b>Torrent Action</b></div>
            <div class="panel-body" id="torrent_action">
                <div class="torrent-action-item"><!--suppress HtmlUnknownTarget -->
                    <a href="/torrent/download?id=<?= $torrent->getId() ?>"><i class="fa fa-download fa-fw"></i>&nbsp;Download Torrent</a>
                </div><!-- Download Torrent -->
                <div class="torrent-action-item">
                    <a class="torrent-favour" href="javascript:" data-tid="<?= $torrent->getId() ?>"><i class="<?= app()->site->getCurUser()->inBookmarkList($torrent->getId()) ? 'fas' : 'far' ?> fa-star fa-fw"></i>&nbsp;Add to Favour</a>
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
                <?php if($torrent->hasNfo()): // TODO add global config key of NFO ?>
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
        <div class="panel" id="torrent_info_panel">
            <div class="panel-heading"><b>Torrent Information</b></div>
            <div class="panel-body" id="torrent_information">
                <div data-field="added_date" data-timestamp="<?= strtotime($torrent->getAddedAt()) ?>">
                    <b>Uploaded Date:</b> <?= $torrent->getAddedAt() ?></div>
                <div data-field="size" data-filesize="<?= $torrent->getTorrentSize() ?>">
                    <b>File size:</b> <?= $this->e($torrent->getTorrentSize(), 'format_bytes') ?></div>
                <div data-field="uploader" data-owner-id="<?= $torrent->getUplver() ? 0 : $torrent->getOwnerId(); ?>">
                    <b>Uploader:</b> <?= $this->insert('helper/username', ['user' => $torrent->getOwner(), 'torrent' => $torrent]) ?>
                </div>
                <div data-field="peers" data-seeders="<?= $torrent->getComplete() ?>" data-leechers="<?= $torrent->getComplete() ?>" data-completed="<?= $torrent->getDownloaded() ?>">
                    <b>Peers:</b>
                    <span class="green"><i class="fas fa-arrow-up fa-fw"></i> <?= $torrent->getComplete() ?></span> /
                    <span class="red"><i class="fas fa-arrow-down fa-fw"></i> <?= $torrent->getIncomplete() ?></span> /
                    <span><i class="fas fa-check fa-fw"></i> <?= $torrent->getDownloaded() ?></span>
                </div>
                <div data-field="info_hash" data-infohash="<?= $torrent->getInfoHash() ?>"><b>Info Hash:</b>
                    <kbd><?= $torrent->getInfoHash() ?></kbd></div>
            </div>
        </div>
        <div class="panel" id="torrent_tags_panel">
            <div class="panel-heading"><b>Torrent Tags</b></div>
            <div class="panel-body" id="torrent_tags">
                <?php $tags = $torrent->getTags(); ?>
                <?php if (count($tags) > 0) : ?>
                    <?php foreach ($tags as $tag): ?>
                        <a href="/torrents/tags?tag=<?= $tag['tag'] ?>" class="label label-outline <?= $tag['class_name'] ?>"><?= $tag['tag'] ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-muted">No tags for this torrent</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
