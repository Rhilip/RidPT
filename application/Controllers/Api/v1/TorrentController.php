<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 10:39
 */

namespace App\Controllers\Api\v1;

use App\Models\Api\v1\Form\TorrentsForm;

class TorrentController
{
    public function bookmark()
    {
        $bookmark = new TorrentsForm();
        $bookmark->setInput(container()->get('request')->request->all());
        $success = $bookmark->validate();
        if (!$success) {
            return [
                'success' => false,
                'errors' => $bookmark->getErrors()
            ];
        } else {
            $ret = $bookmark->updateRecord();
            return array_merge(
                ['success' => true],
                $ret
            );
        }
    }

    public function fileList()
    {
        $filelist = new TorrentsForm();
        $filelist->setInput(container()->get('request')->query->all());
        $success = $filelist->validate();
        if (!$success) {
            return [
                'success' => false,
                'errors' => $filelist->getErrors()
            ];
        } else {
            $ret = $filelist->getFileList();
            return array_merge(
                ['success' => true],
                $ret
            );
        }
    }
}
