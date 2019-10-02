<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 16:53
 */

namespace App\Controllers;

use App\Models\Form\Torrent;

use Rid\Http\Controller;

use Exception;

class TorrentController extends Controller
{
    public function actionUpload()
    {
        // TODO Check user upload pos
        if (app()->request->isPost()) {
            $uploadForm = new Torrent\UploadForm();
            $uploadForm->setInput(app()->request->post());
            $uploadForm->setFileInput(app()->request->files());
            $success = $uploadForm->validate();
            if (!$success) {
                return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $uploadForm->getError()]);
            } else {
                try {
                    $uploadForm->flush();
                } catch (Exception $e) {
                    return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
                }

                return app()->response->redirect('/torrent/details?id=' . $uploadForm->getId());
            }
        } else {
            return $this->render('torrent/upload');
        }
    }

    public function actionDetails()
    {
        $details = new Torrent\DetailsForm();
        $success = $details->validate();
        if (!$success) {
            return $this->render('action/fail', ['msg' => $details->getError()]);
        }

        return $this->render('torrent/details', ['details' => $details]);
    }

    public function actionEdit() // TODO
    {
        $edit = new Torrent\EditForm();

        if (app()->request->isPost()) {
            $edit->setInput(app()->request->get() + app()->request->post());
            $success = $edit->validate();
            if (!$success) {
                return $this->render('action/fail', ['msg' => $edit->getError()]);
            } else {
                $edit->flush();
                return app()->response->redirect('/torrent/details?id=' . $edit->getTorrent()->getId());
            }
        } else {
            $edit->setInput(app()->request->get());
            $permission_check = $edit->checkUserPermission();
            if ($permission_check === false) {
                return $this->render('action/fail', ['msg' => $edit->getError()]);
            } else {
                return $this->render('torrent/edit', ['edit' => $edit]);
            }
        }
    }

    public function actionSnatch()
    {
        $snatch = new Torrent\SnatchForm();
        $success = $snatch->validate();
        if (!$success) {
            return $this->render('action/fail');
        }

        return $this->render('torrent/snatch', ['snatch' => $snatch]);
    }

    public function actionDownload()
    {
        $downloader = new Torrent\DownloadForm();
        $success = $downloader->validate();
        if (!$success) {
            return $this->render('action/fail');
        }

        return $downloader->sendFileContentToClient();
    }

    public function actionComments()
    {
        $comments = new Torrent\CommentsForm();
        $success = $comments->validate();
        if (!$success) {
            return $this->render('action/fail');
        }

        return $this->render('torrent/comments', ['comments' => $comments]);
    }

    public function actionStructure()
    {
        $structure = new Torrent\StructureForm();
        $success = $structure->validate();
        if (!$success) {
            return $this->render('action/fail');
        }

        return $this->render('torrent/structure', ['structure' => $structure]);
    }
}
