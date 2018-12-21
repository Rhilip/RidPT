<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\httpd\controllers;

use apps\httpd\models\TorrentUploadForm;
use Mix\Facades\Request;
use Mix\Http\Controller;

use SandFoxMe\Bencode\Bencode;

class TorrentsController extends Controller
{
    public function actionIndex()
    {

    }

    public function actionUpload()
    {
        if (Request::isPost()) {
            $model = new TorrentUploadForm();
            $model->attributes = Request::post();
            $model->setScenario('upload');
            if (!$model->validate()) {
                return $this->render("torrents/upload_fail.html.twig", ["msg" => $model->getError()]);
            }
            app()->dump("h123",true);
            if ($model->file->getExtension() !== "torrent")
                return $this->render("torrents/upload_fail.html.twig", ["msg" => "Un-valid torrents files"]);


            //$tmpname = file_get_contents($model->file->tmpName);


            return "Pass";
        } else {
            // TODO Check user can upload
            return $this->render("torrents/upload.html.twig");
        }

    }

    public function actionSearch()
    {

    }
}
