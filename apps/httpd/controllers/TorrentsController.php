<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\httpd\controllers;

use Mix\Facades\PDO;
use Mix\Facades\Request;
use Mix\Facades\Response;
use Mix\Http\Controller;

use apps\httpd\models\form\TorrentUploadForm;


class TorrentsController extends Controller
{

    public function actionIndex()
    {

    }

    public function actionUpload()
    {
        // TODO Check user upload pos
        if (Request::isPost()) {
            $torrent = new TorrentUploadForm();
            $torrent->importAttributes(Request::post());
            $torrent->importFileAttributes(Request::files());
            $error = $torrent->validate();
            if (count($error) > 0) {
                return $this->render("torrents/upload_fail.html.twig", ["msg" => $torrent->getError()]);
            } else {
                try {
                    $torrent->flush();
                } catch (\Exception $e) {
                    return $this->render("torrents/upload_fail.html.twig", ["msg" => $e->getMessage()]);
                }


                return Response::redirect("/torrents/details?id=" . $torrent->id);
            }

        } else {
            // TODO Check user can upload
            return $this->render("torrents/upload.html.twig");
        }

    }

    public function actionSearch()
    {

    }

    public function actionDetail()
    {
        $tid = Request::get('id');

        $data = PDO::createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
            'id' => $tid
        ])->queryOne();


    }
}
