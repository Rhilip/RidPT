<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 9:57 PM
 */

namespace apps\controllers;

use apps\models\form\Subtitles;

use Rid\Http\Controller;

class SubtitlesController extends Controller
{
    public function actionIndex()
    {
        return $this->actionSearch();
    }

    public function actionSearch($upload = null)
    {
        $search = new Subtitles\SearchForm();
        if (false === $success = $search->validate()) {
            return $this->render('action/action_fail', ['msg' => $search->getError()]);
        }
        return $this->render('subtitles/search', ['search' => $search, 'upload_mode' => $upload]);
    }

    public function actionUpload()
    {
        if (app()->request->isPost()) {
            $upload = new Subtitles\UploadForm();
            if (false === $success = $upload->validate()) {
                return $this->render('action/action_fail', ['msg' => $upload->getError()]);   // TODO add redirect
            } else {
                $upload->flush();
                return $this->render('action/action_success');  // TODO add redirect
            }
        }

        return $this->actionSearch(true);
    }

    public function actionDownload()
    {
        $download = new Subtitles\DownloadForm();
        if (false === $success = $download->validate()) {
            return $this->render('action/action_fail', ['msg' => $download->getError()]);
        }

        return $download->sendFileContentToClient();
    }

    public function actionDelete()
    {
        $delete = new Subtitles\DeleteForm();
        if (false === $success = $delete->validate()) {
            return $this->render('action/action_fail', ['msg' => $delete->getError()]);  // TODO add redirect
        } else {
            $delete->flush();
            return $this->render('action/action_success',['redirect' => '/subtitles']); // TODO add redirect
        }
    }
}
