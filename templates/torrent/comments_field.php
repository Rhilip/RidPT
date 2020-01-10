<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 8:58 PM
 *
 * @var array $comments
 * @var \App\Entity\Torrent $torrent
 */

$enabled_editor = $enabled_editor ?? false;
?>

<div class="comments">
    <?php if ($torrent->getComments()): ?>
        <section class="comments-list">
            <?php foreach ($comments as $commit): ?>
                <?php
                $commit_user = app()->site->getUser($commit['owner_id']);

                // The details of commentator should be hide or not ?
                $commentator_hide_flag = $torrent->getUplver() &&  // The torrent is uplver
                    $commit['owner_id'] == $torrent->getOwnerId(); // Commentator is the uploader for this torrent
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
                                <span>Edited at <?= $commit['edit_at'] ?> </span> <!-- TODO show old edit log -->
                            <?php else: ?>
                                <span>Created at <?= $commit['create_at'] ?> </span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-username">
                            <?= $this->insert('helper/username', ['user' => $commit_user, 'hide' => $commentator_hide_flag, 'show_badge' => true]) ?>
                        </div>
                        <div class="text ubbcode-block"><?= $this->batch($commit['text'], 'format_ubbcode') ?></div>
                        <div class="actions pull-right"> <!-- TODO -->
                            <a href="#">Report</a>
                            <a href="#">Reply</a>
                            <a href="#">Edit</a>
                            <a href="#">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    <footer>
        <div class="reply-form" id="commentReplyForm1">
            <div class="avatar"><img src="<?= app()->auth->getCurUser()->getAvatar() ?>" alt=""></div>
            <form class="form" method="post" action="/torrent/comments?id=<?= $torrent->getId() ?>">
                <!-- FIXME commit point -->
                <div class="form-group">
                    <textarea class="form-control new-comment-text<?= $enabled_editor ? ' to-load-editor': '' ?>" rows="3" placeholder="Quick Commit Here" data-autoresize></textarea>
                </div>
                <div class="form-group comment-user">
                    <div class="row">
                        <div class="col-md-10">
                            <div id="quote_help_block"></div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-block btn-primary"><i class="far fa-paper-plane"></i>&nbsp;&nbsp;Send
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </footer>
</div>
