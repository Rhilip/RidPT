<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 6:58 PM
 */

namespace App\Models\Form\Torrent;

use Rhilip\Bencode\Bencode;
use Rid\Helpers\ContainerHelper;

class StructureForm extends DetailsForm
{
    public function getTorrentFileContentDict()
    {
        $file_loc = ContainerHelper::getContainer()->get('path.storage.torrents') . $this->id . '.torrent';
        return Bencode::load($file_loc);
    }
}
