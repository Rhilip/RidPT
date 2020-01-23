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
use Symfony\Component\HttpFoundation\Request;

class TorrentController extends Controller
{
    public function actionUpload()
    {
        // TODO Check user upload pos
        if (app()->request->isMethod(Request::METHOD_POST)) {
            $uploadForm = new Torrent\UploadForm();
            $uploadForm->setInput(app()->request->request->all());
            $uploadForm->setFileInput(app()->request->raw_files);
            $success = $uploadForm->validate();
            if (!$success) {
                return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $uploadForm->getError()]);
            } else {
                try {
                    $uploadForm->flush();
                } catch (Exception $e) {
                    return $this->render('action/fail', ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
                }

                return app()->response->setRedirect('/torrent/details?id=' . $uploadForm->getId());
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

        if (app()->request->isMethod(Request::METHOD_POST)) {
            $edit->setInput(app()->request->query->all() + app()->request->request->all());
            $success = $edit->validate();
            if (!$success) {
                return $this->render('action/fail', ['msg' => $edit->getError()]);
            } else {
                $edit->flush();
                return app()->response->setRedirect('/torrent/details?id=' . $edit->getTorrent()->getId());
            }
        } else {
            $edit->setInput(app()->request->query->all());
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
