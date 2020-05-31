<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 10:03 PM
 */

namespace App\Models\Form\Subtitles;

use App\Models\Form\Traits;

use Rid\Validators\Validator;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadForm extends Validator
{
    use Traits\isValidSubtitleTrait;

    private function addDownloadHit()
    {
        container()->get('pdo')->prepare('UPDATE `subtitles` SET `hits` = `hits` + 1 WHERE id = :sid')->bindParams([
            'sid' => $this->id
        ])->execute();
    }

    public function sendFileContentToClient()
    {
        // FIXME
        $this->addDownloadHit();

        $filename = $this->id . '.' . $this->subtitle['ext'];
        $file_loc = container()->get('path.storage.subs') . DIRECTORY_SEPARATOR . $filename;

        container()->get('response')->setFile($file_loc);
        container()->get('response')->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->subtitle['filename']
        );
    }
}
