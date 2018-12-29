<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\httpd\controllers;

use apps\httpd\models\Torrent;

use Mix\Facades\Config;
use Mix\Facades\PDO;
use Mix\Facades\Request;
use Mix\Facades\Response;
use Mix\Facades\Session;

use Mix\Http\Controller;

use apps\httpd\models\form\TorrentUploadForm;

use SandFoxMe\Bencode\Bencode;


class TorrentsController extends Controller
{

    public function actionIndex()
    {
        $fetch = PDO::createCommand("SELECT `id` FROM torrents LIMIT 50;")->queryAll();

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

    public function actionDetails()
    {
        $tid = Request::get('id');

        $torrent = new Torrent($tid);

        return $this->render("torrents/details.html.twig", ["torrent" => $torrent]);
    }

    public function actionDownload()
    {
        $tid = Request::get('id');
        $userInfo = Session::get('userInfo');  // FIXME add remote download by &passkey=  (Add change our BeforeMiddle) or token ?

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        $filename = "[" . Config::get("base.site_name") . "]" . $torrent->getTorrentName() . ".torrent";
        $file = $torrent::TorrentFileLoc($tid);
        $dict = Bencode::load($file);

        $scheme = "http://";
        if (filter_var(Request::get("https"), FILTER_VALIDATE_BOOLEAN))
            $scheme = "https://";
        else if (filter_var(Request::get("https"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
            $scheme = "http://";
        else if (Request::isSecure())
            $scheme = "https://";

        // FIXME bad code
        $passkey = PDO::createCommand("SELECT `passkey` FROM `users` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $userInfo["uid"]
        ])->queryScalar();

        $announce_suffix = "/announce?passkey=" . $passkey;
        $dict["announce"] = $scheme . Config::get("base.site_tracker_url") . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * See more on : http://www.bittorrent.org/beps/bep_0012.html
         */
        if ($muti_tracker = Config::get("base.site_muti_tracker_url")) {
            $dict["announce-list"] = [];

            // Add our main tracker into muti_tracker_list to avoid lost error....
            $muti_tracker = Config::get("base.site_tracker_url") . "," . $muti_tracker;

            $muti_tracker_list = explode(",", $muti_tracker);
            foreach (array_unique($muti_tracker_list) as $tracker) {  // use array_unique to remove dupe tracker
                $dict["announce-list"][] = [$scheme . $tracker . $announce_suffix];
            }
        }

        Response::setHeader("Content-Type", "application/x-bittorrent");
        if (strpos(Request::header("user-agent"), "IE")) {
            Response::setHeader("Content-Disposition", "attachment; filename=" . str_replace("+", "%20", rawurlencode($filename)));
        } else {
            Response::setHeader("Content-Disposition", "attachment; filename=\"$filename\" ; charset=utf-8");
        }

        return Bencode::encode($dict);
    }
}
