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

include 'helper.php';
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?><?= $torrent->getTitle() ?><?php $this->end();?>

<?php $this->start('container')?>
<h2 class="text-center"><?= $torrent->getTitle() ?></h2>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Torrent Information</h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-md-1"><b>Subtitle: </b></div>
            <div class="col-md-11"><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></div>
        </div>

        <div class="row">
            <div class="col-md-1"><b>Date: </b></div>
            <div class="col-md-5" data-timestamp="{{ torrent.getAddedAt() }}"><?= $torrent->getAddedAt() ?></div>

            <div class="col-md-1">File size:</div>
            <div class="col-md-5"><?= $this->e($torrent->getTorrentSize(),'format_bytes') ?></div>
        </div>

        <div class="row">
            <div class="col-md-1">Uploader: </div>
            <div class="col-md-5"><?= get_torrent_uploader($torrent) ?></div>
            <div class="col-md-1">Seeders:</div>
            <div class="col-md-5"><span style="color: green;"><?= $torrent->getComplete() ?></span></div>
        </div>

        <div class="row">
            <div class="col-md-1">Leechers:</div>
            <div class="col-md-5"><span style="color: red;"><?= $torrent->getIncomplete() ?></span></div>

            <div class="col-md-1">Completed:</div>
            <div class="col-md-5"><?= $torrent->getDownloaded() ?></div>
        </div>
        <div class="row">
            <div class="col-md-offset-6 col-md-1">Info hash:</div>
            <div class="col-md-5"><kbd><?= $torrent->getInfoHash() ?></kbd></div> <!-- TODO -->
        </div>
    </div><!--/.panel-body -->

    <div class="panel-footer clearfix">
        <!--suppress HtmlUnknownTarget --><a href="/torrents/download?id={{ torrent.getId() }}"><i class="fa fa-download fa-fw"></i>Download Torrent</a> |
        <!--suppress HtmlUnknownTarget --><a href="/torrents/favour?id={{ torrent.getId() }}"><i class="fa fa-star fa-fw"></i>Add to Favour</a> | <!-- add remove from Favour -->
        <!--suppress HtmlUnknownTarget --><a href="/report?type=torrent&id={{ torrent.getId() }}"><i class="fa fa-bug fa-fw"></i>Report this Torrent</a>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body" id="torrent-description">
        <?= $torrent->getDescr() ?: '<h4>No description.</h4>' ?>
    </div>
</div>
<?php $this->end();?>
