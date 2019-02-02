<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\controllers;

use Rid\Http\Controller;

use apps\models\Torrent;
use apps\models\form\TorrentUploadForm;


class TorrentsController extends Controller
{

    public function actionIndex()
    {
        // TODO add pagination support
        $fetch = app()->pdo->createCommand("SELECT `id` FROM torrents ORDER BY added_at DESC LIMIT 50;")->queryAll();

        $torrents = array_map(function ($id) {
            return new Torrent($id);
        }, $fetch);

        return $this->render("torrents/list.html.twig", [
            "torrents" => $torrents
        ]);

    }

    public function actionUpload()
    {
        // TODO Check user upload pos
        if (app()->request->isPost()) {
            $torrent = new TorrentUploadForm();
            $torrent->importAttributes(app()->request->post());
            $torrent->importFileAttributes(app()->request->files());
            $error = $torrent->validate();
            if (count($error) > 0) {
                return $this->render("errors/action_fail.html.twig", ['title' => 'Upload Failed', 'msg' => $torrent->getError()]);
            } else {
                try {
                    $torrent->flush();
                } catch (\Exception $e) {
                    return $this->render("errors/action_fail.html.twig", ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
                }

                return app()->response->redirect("/torrents/details?id=" . $torrent->id);
            }

        } else {
            // TODO Check user can upload
            return $this->render("torrents/upload.html.twig");
        }

    }

    public function actionSearch()
    {

    }

    public function actionDetails()
    {
        $tid = app()->request->get('id');

        $torrent = new Torrent($tid);

        return $this->render("torrents/details.html.twig", ["torrent" => $torrent]);
    }

    public function actionDownload()
    {
        $tid = app()->request->get('id');

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        $filename = "[" . app()->config->get("base.site_name") . "]" . $torrent->getTorrentName() . ".torrent";

        app()->response->setHeader("Content-Type", "application/x-bittorrent");
        if (strpos(app()->request->header("user-agent"), "IE")) {
            app()->response->setHeader("Content-Disposition", "attachment; filename=" . str_replace("+", "%20", rawurlencode($filename)));
        } else {
            app()->response->setHeader("Content-Disposition", "attachment; filename=\"$filename\" ; charset=utf-8");
        }

        return $torrent->getDownloadDict(true);
    }
}
