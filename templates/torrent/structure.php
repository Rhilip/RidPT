<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 17:10
 *
 * @var League\Plates\Template\Template $this
 * @var \App\Models\Form\Torrent\StructureForm $structure
 */

use Rhilip\Bencode\Bencode;

if (!function_exists('torrent_structure_builder')) {
    function torrent_structure_builder($array, $parent = "")
    {
        $ret = '';
        foreach ($array as $item => $value) {
            $value_length = strlen(Bencode::encode($value));
            if (is_iterable($value)) {  // It may `dictionary` or `list`
                $type = is_indexed_array($value) ? 'list' : 'dictionary';
                $ret .= "<li" . (($item=='root' || $item == 'info') ? ' class="open"' : '') . "><a href='#'><span class='title'>[" . $item . "]</span> <span class='type' data-type='" . $type ."'>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span></a>";
                $ret .= "<ul>" . torrent_structure_builder($value, $item) . "</ul></li>";
            } else { // It may `integer` or `string`
                $type = is_integer($value) ? 'integer' : 'string';
                $value = ($parent == 'info' && $item == 'pieces') ? "0x" . bin2hex(substr($value, 0, 25)) . "..." : $value;  // Cut the info pieces....
                $ret .= "<li><span class='title'>[" . $item . "]</span> <span class='type' data-type='" . $type ."'>(" . ucfirst($type) . ")</span> <span class=length>[" . $value_length . "]</span>: <span class=value>" . $value . "</span></li>";
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
                    <ul class="tree tree-lines tree-angles" data-ride="tree" data-animate="true">
                        <?= torrent_structure_builder(['root' => $structure->getTorrentFileContentDict()]); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
