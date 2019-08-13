<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 6:58 PM
 */

namespace apps\models\form\Torrent;

use Rid\Bencode\Bencode;

class StructureForm extends DetailsForm
{

    public function getTorrentFileContentDict()
    {
        $file_loc = app()->site->getTorrentFileLoc($this->getInputId());
        $content = Bencode::load($file_loc);
        return $content;
    }

}
