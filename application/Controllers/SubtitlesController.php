<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 9:57 PM
 */

namespace App\Controllers;

use App\Models\Form\Subtitles;

use Rid\Http\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class SubtitlesController extends AbstractController
{
    public function search($upload = null)
    {
        $search = new Subtitles\SearchForm();
        $search->setInput(container()->get('request')->query->all());
        if (false === $success = $search->validate()) {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }
        return $this->render('subtitles/search', ['search' => $search, 'upload_mode' => $upload]);
    }

    public function upload()
    {
        if (container()->get('request')->isMethod(Request::METHOD_POST)) {
            $upload = new Subtitles\UploadForm();
            $upload->setInput(container()->get('request')->request->all() + container()->get('request')->files->all());
            if (false === $success = $upload->validate()) {
                return $this->render('action/fail', ['msg' => $upload->getError()]);   // TODO add redirect
            } else {
                $upload->flush();
                return $this->render('action/success');  // TODO add redirect
            }
        }

        return $this->search(true);
    }

    public function download()
    {
        $download = new Subtitles\DownloadForm();
        $download->setInput(container()->get('request')->query->all());
        if (false === $success = $download->validate()) {
            return $this->render('action/fail', ['msg' => $download->getError()]);
        }

        return $download->sendFileContentToClient();
    }

    public function delete()
    {
        $delete = new Subtitles\DeleteForm();
        $delete->setInput(container()->get('request')->request->all());
        if (false === $success = $delete->validate()) {
            return $this->render('action/fail', ['msg' => $delete->getError()]);  // TODO add redirect
        } else {
            $delete->flush();
            return $this->render('action/success', ['redirect' => '/subtitles']); // TODO add redirect
        }
    }
}
