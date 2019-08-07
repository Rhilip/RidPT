<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 16:53
 */

namespace apps\controllers;

use apps\models\form\Torrent;

use Rid\Http\Controller;

class TorrentController extends Controller
{
    public function actionDetails()
    {
        $details = new Torrent\DetailsForm();
        $details->setData(app()->request->get());
        $success = $details->validate();
        if (!$success) {
            return $this->render('action/action_fail');
        }

        return $this->render('torrent/details', ['details' => $details]);
    }

    public function actionEdit() // TODO
    {

    }

    public function actionSnatch()
    {
        $snatch = new Torrent\SnatchForm();
        $snatch->setData(app()->request->get());
        $success = $snatch->validate();
        if (!$success) {
            return $this->render('action/action_fail');
        }

        return $this->render('torrent/snatch', ['snatch' => $snatch]);
    }

    public function actionDownload()
    {
        $downloader = new Torrent\DownloadForm();
        $downloader->setData(app()->request->get());
        $success = $downloader->validate();
        if (!$success) {
            return $this->render('action/action_fail');
        }

        $downloader->setRespHeaders();
        return $downloader->getDownloadDict();
    }

    public function actionComments()
    {
        // TODO
    }

    public function actionStructure()
    {
        $structure = new Torrent\StructureForm();
        $structure->setData(app()->request->get());
        $success = $structure->validate();
        if (!$success) {
            return $this->render('action/action_fail');
        }

        return $this->render('torrent/structure', ['structure' => $structure]);
    }
}
