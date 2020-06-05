<?php
/** TODO
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 4:01 PM
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrent\CommentsForm $comments
 */
$torrent = $comments->getTorrent();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Torrents Snatched Details<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel" id="torrent_comment_details_panel">
            <div class="panel-heading"><b>Torrent Comments</b></div>
            <div class="panel-body" id="torrent_comment_details_body">
                <div id="torrent_comment_details">
                    <?= $this->insert('torrents/comments_field', ['torrent' => $torrent, 'comments' => $comments->getPagerData(),'enabled_editor' => true]) ?>
                </div>
                <div class="text-center">
                    <ul class="pager" data-ride="remote_pager" data-rec-total="<?= $comments->getTotal() ?>" data-rec-per-page="<?= $comments->getLimit() ?>"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>
