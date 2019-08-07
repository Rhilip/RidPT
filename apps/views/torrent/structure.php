<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 17:10
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\form\Torrent\StructureForm $structure
 */

use Rid\Bencode\Bencode;

if (!function_exists('torrent_structure_builder')) {
    function torrent_structure_builder($array, $parent = "")
    {
        $ret = '';
        foreach ($array as $item => $value) {
            $value_length = strlen(Bencode::encode($value));
            if (is_iterable($value)) {  // It may `dictionary` or `list`
                $type = is_indexed_array($value) ? 'list' : 'dictionary';
                $ret .= "<li><div align='left' class='" . $type . "'><a href='javascript:'> + <span class=title>[" . $item . "]</span> <span class='icon'>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span></a></div>";
                $ret .= "<ul style='display:none'>" . torrent_structure_builder($value, $item) . "</ul></li>";
            } else { // It may `interger` or `string`
                $type = is_integer($value) ? 'integer' : 'string';
                $value = ($parent == 'info' && $item == 'pieces') ? "0x" . bin2hex(substr($value, 0, 25)) . "..." : $value;  // Cut the info pieces....
                $ret .= "<li><div align=left class=" . $type . "> - <span class=title>[" . $item . "]</span> <span class=icon>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span>: <span class=value>" . $value . "</span></div></li>";
            }
        }
        return $ret;
    }
}

$torrent = $structure->getTorrent();
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents Structure<?php $this->end();?>

<?php $this->start('container')?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <div class="panel"  id="torrent_structure_panel">
            <div class="panel-heading"><b>Torrent Structure</b></div>
            <div class="panel-body" id="torrent_structure_body">
                <div id="torrent_structure">
                <?= torrent_structure_builder(['root' => $structure->getTorrentFileContentDict()]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
