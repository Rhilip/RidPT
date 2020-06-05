<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 10:29 AM
 *
 * @var League\Plates\Template\Template $this
 * @var App\Forms\Torrents\NfoForm $nfo
 */

$torrent = $nfo->getTorrent();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title') ?>Torrents Nfo review<?php $this->end(); ?>

<?php $this->start('container') ?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>
<div class="row">
    <div class="text-center">
        <a href="?id=<?= $nfo->getInput('id') ?>&view=magic">DOS 样式</a> |
        <a href="?id=<?= $nfo->getInput('id') ?>&view=latin-1">Windows 样式</a>
    </div>
    <div class="col-md-10 col-md-offset-1">
        <div class="panel" id="torrent_nfo_panel">
            <div class="panel-heading"><b>Torrent Nfo</b></div>
            <div class="panel-body" id="torrent_nfo_body">
                <pre id="torrent_nfo"
                     style="<?= $nfo->getInput('view') == 'fonthack' ? "font-size:10pt; font-family: 'MS LineDraw', 'Terminal', monospace;" : "font-size:10pt; font-family: 'Courier New', monospace;" ?>"
                ><?= $nfo->getNfo() ?></pre>
            </div>
        </div>
    </div>
</div>
<?php $this->end(); ?>

