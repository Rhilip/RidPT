<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 17:10
 *
 * @var League\Plates\Template\Template $this
 * @var \apps\models\Torrent $torrent
 */

use Rid\Bencode\Bencode;

function torrent_structure_builder($array, $parent = "")
{
    $ret = '';
    foreach ($array as $item => $value) {
        $value_length = strlen(Bencode::encode($value));
        if (is_iterable($value)) {  // It may `dictionary` or `list`
            $type = is_indexed_array($value) ? 'list' : 'dictionary';
            $ret .= "<li><div align='left' class='".$type ."'><a href='javascript:'> + <span class=title>[" . $item . "]</span> <span class='icon'>(" . ucfirst($type) . ")</span> <span class=length>[". $value_length . "]</span></a></div>";
            $ret .= "<ul style='display:none'>" . torrent_structure_builder($value,$item) . "</ul></li>";
        } else { // It may `interger` or `string`
            $type = is_integer($value) ? 'integer' : 'string';
            $value = ($parent == 'info' && $item == 'pieces') ? "0x".bin2hex(substr($value, 0, 25))."..." : $value;  // Cut the info pieces....
            $ret .="<li><div align=left class=". $type ."> - <span class=title>[". $item . "]</span> <span class=icon>(". ucfirst($type).")</span> <span class=length>[". $value_length ."]</span>: <span class=value>" . $value ."</span></div></li>";
        }
    }
    return $ret;
}
?>

<?= $this->layout('layout/base') ?>

<?php $this->start('title')?>Torrents Structure<?php $this->end();?>

<?php $this->start('container')?>
<div class="text-center torrent-title-block">
    <h1 class="torrent-title"><?= $torrent->getTitle() ?></h1>
    <small class="torrent-subtitle"><em><?= $torrent->getSubtitle() ?: 'No Subtitle.' ?></em></small>
</div>

<div class="layui-row">
    <div class="layui-col-md8 layui-col-md-offset2">
        <div class="layui-card" id="torrent-structure-card">
            <div class="layui-card-header"><b>Torrent Structure</b></div>
            <div class="layui-card-body" id="torrent-structure-body">
                <ul id="torrent-structure">
                    <?= torrent_structure_builder(['root' => $torrent->getRawDict()]); ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $this->end();?>
