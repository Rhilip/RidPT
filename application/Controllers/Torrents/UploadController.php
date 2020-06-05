<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 9:21 PM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\UploadForm;
use Rid\Http\AbstractController;

class UploadController extends AbstractController
{
    public function index()
    {
        return $this->render('torrents/upload');
    }

    /** @noinspection PhpUnused */
    public function takeUpload()
    {
        $uploadForm = new UploadForm();
        $uploadForm->setInput(container()->get('request')->request->all() + container()->get('request')->files->all());
        if ($uploadForm->validate()) {
            try {
                $uploadForm->flush();
            } catch (\Exception $e) {
                return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
            }
        }

        if ($uploadForm->getId() > 0) {
            return container()->get('response')->setRedirect('/torrents/detail?id=' . $uploadForm->getId());
        }
        return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $uploadForm->getError()]);
    }
}
