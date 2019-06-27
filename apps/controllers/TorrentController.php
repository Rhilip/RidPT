<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 16:53
 */

namespace apps\controllers;

use apps\models\Torrent;
use apps\models\form\TorrentUploadForm;

use Rid\Http\Controller;

class TorrentController extends Controller
{
    public function actionDetails()
    {
        $tid = app()->request->get('id');
        $torrent = new Torrent($tid);

        return $this->render('torrent/details', ['torrent' => $torrent]);
    }

    public function actionUpload()
    {
        // TODO Check user upload pos
        if (app()->request->isPost()) {
            $torrent = new TorrentUploadForm();
            $torrent->setData(app()->request->post());
            $torrent->setFileData(app()->request->files());
            $success = $torrent->validate();
            if (!$success) {
                return $this->render('errors/action_fail', ['title' => 'Upload Failed', 'msg' => $torrent->getError()]);
            } else {
                try {
                    $torrent->flush();
                } catch (\Exception $e) {
                    return $this->render('errors/action_fail', ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
                }

                return app()->response->redirect('/torrent/details?id=' . $torrent->id);
            }

        } else {
            // TODO Check user can upload
            return $this->render('torrent/upload');
        }

    }

    public function actionDownload()
    {
        $tid = app()->request->get('id');

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        $filename = '[' . config('base.site_name') . ']' . $torrent->getTorrentName() . '.torrent';

        app()->response->setHeader('Content-Type', 'application/x-bittorrent');
        if (strpos(app()->request->header('user-agent'), 'IE')) {
            app()->response->setHeader('Content-Disposition', 'attachment; filename=' . str_replace('+', '%20', rawurlencode($filename)));
        } else {
            app()->response->setHeader('Content-Disposition', "attachment; filename=\"$filename\" ; charset=utf-8");
        }

        return $torrent->getDownloadDict(true);
    }

    public function actionStructure()
    {
        $tid = app()->request->get('id');

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        return $this->render('torrent/structure', ['torrent' => $torrent]);
    }
}
