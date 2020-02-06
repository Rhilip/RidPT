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

class DownloadForm extends Validator
{
    use Traits\FileSentTrait, Traits\isValidSubtitleTrait;

    protected $_autoload = true;
    protected $_autoload_from = ['get'];

    private function addDownloadHit()
    {
        app()->pdo->prepare('UPDATE `subtitles` SET `hits` = `hits` + 1 WHERE id = :sid')->bindParams([
            'sid' => $this->id
        ])->execute();
    }

    protected function getSendFileName(): string
    {
        return $this->subtitle['filename'];
    }

    protected function hookFileContentSend()
    {
        $this->addDownloadHit();
    }

    protected function getSendFileContent()
    {
        $filename = $this->id . '.' . $this->subtitle['ext'];
        $file_loc = app()->getStoragePath('subs') . DIRECTORY_SEPARATOR . $filename;
        app()->response->setFile($file_loc);
    }
}
