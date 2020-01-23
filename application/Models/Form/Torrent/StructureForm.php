<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 6:58 PM
 */

namespace App\Models\Form\Torrent;

use App\Libraries\Constant;

use Rhilip\Bencode\Bencode;

class StructureForm extends DetailsForm
{
    public function getTorrentFileContentDict()
    {
        $file_loc = Constant::getTorrentFileLoc($this->id);
        $content = Bencode::load($file_loc);
        return $content;
    }
}
