<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\httpd\controllers;

use Mix\Http\Controller;

use apps\httpd\models\Torrent;
use apps\httpd\models\form\TorrentUploadForm;

use SandFoxMe\Bencode\Bencode;


class TorrentsController extends Controller
{

    public function actionIndex()
    {
        $fetch = app()->pdo->createCommand("SELECT `id` FROM torrents LIMIT 50;")->queryAll();

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
                return $this->render("torrents/upload_fail.html.twig", ["msg" => $torrent->getError()]);
            } else {
                try {
                    $torrent->flush();
                } catch (\Exception $e) {
                    return $this->render("torrents/upload_fail.html.twig", ["msg" => $e->getMessage()]);
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
        $userInfo = app()->session->get('userInfo');  // FIXME add remote download by &passkey=  (Add change our BeforeMiddle) or token ?

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        $filename = "[" . app()->config->get("base.site_name") . "]" . $torrent->getTorrentName() . ".torrent";
        $file = $torrent::TorrentFileLoc($tid);
        $dict = Bencode::load($file);

        $scheme = "http://";
        if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN))
            $scheme = "https://";
        else if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
            $scheme = "http://";
        else if (app()->request->isSecure())
            $scheme = "https://";

        // FIXME bad code
        $passkey = app()->pdo->createCommand("SELECT `passkey` FROM `users` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $userInfo["uid"]
        ])->queryScalar();

        $announce_suffix = "/announce?passkey=" . $passkey;
        $dict["announce"] = $scheme . app()->config->get("base.site_tracker_url") . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * See more on : http://www.bittorrent.org/beps/bep_0012.html
         */
        if ($muti_tracker = app()->config->get("base.site_muti_tracker_url")) {
            $dict["announce-list"] = [];

            // Add our main tracker into muti_tracker_list to avoid lost error....
            $muti_tracker = app()->config->get("base.site_tracker_url") . "," . $muti_tracker;

            $muti_tracker_list = explode(",", $muti_tracker);
            foreach (array_unique($muti_tracker_list) as $tracker) {  // use array_unique to remove dupe tracker
                $dict["announce-list"][] = [$scheme . $tracker . $announce_suffix];
            }
        }

        app()->response->setHeader("Content-Type", "application/x-bittorrent");
        if (strpos(app()->request->header("user-agent"), "IE")) {
            app()->response->setHeader("Content-Disposition", "attachment; filename=" . str_replace("+", "%20", rawurlencode($filename)));
        } else {
            app()->response->setHeader("Content-Disposition", "attachment; filename=\"$filename\" ; charset=utf-8");
        }

        return Bencode::encode($dict);
    }
}
