<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 6:58 PM
 */

namespace App\Models\Form\Torrent;

use Rhilip\Bencode\Bencode;

class StructureForm extends DetailsForm
{
    public function getTorrentFileContentDict()
    {
        $file_loc = container()->get('path.storage.torrents') . DIRECTORY_SEPARATOR . $this->id . '.torrent';
        return Bencode::load($file_loc);
    }
}
