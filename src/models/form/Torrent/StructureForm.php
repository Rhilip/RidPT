<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 6:58 PM
 */

namespace apps\models\form\Torrent;

use apps\libraries\Constant;
use Rid\Bencode\Bencode;

class StructureForm extends DetailsForm
{

    public function getTorrentFileContentDict()
    {
        $file_loc = Constant::getTorrentFileLoc($this->id);
        $content = Bencode::load($file_loc);
        return $content;
    }

}
