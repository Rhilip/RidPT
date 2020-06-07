<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 10:59 PM
 */

declare(strict_types=1);

namespace App\Controllers\Api\v1\Torrent;

use App\Forms\Api\v1\Torrents\FileListForm;

class FileListController
{
    public function index()
    {
        $fileListForm = new FileListForm();
        $fileListForm->setInput(container()->get('request')->query->all());
        if ($fileListForm->validate()) {
            $fileListForm->flush();
            return [
                'success' => true,
                'msg' => 'Get Filelist success',
                'result' => $fileListForm->getStructure()
            ];
        } else {
            return [
                'success' => false,
                'errors' => $fileListForm->getErrors()
            ];
        }
    }
}
