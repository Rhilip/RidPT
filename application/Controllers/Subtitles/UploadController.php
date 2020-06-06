<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:05 AM
 */

declare(strict_types=1);

namespace App\Controllers\Subtitles;

use App\Forms\Subtitles\UploadForm;

class UploadController extends SearchController
{
    public function index($upload = null)
    {
        return parent::index(true);
    }

    public function takeUpload()
    {
        $upload = new UploadForm();
        $upload->setInput(container()->get('request')->request->all() + container()->get('request')->files->all());
        if ($upload->validate()) {
            $upload->flush();
            return $this->render('action/success');  // TODO add redirect
        } else {
            return $this->render('action/fail', ['msg' => $upload->getError()]);
        }
    }
}
