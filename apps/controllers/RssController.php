<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 9:14
 */

namespace apps\controllers;

use apps\models\Torrent;

use Rid\Http\Controller;

class RssController extends Controller
{
    public function actionIndex()
    {
        // FIXME add torrent search
        $fetch = app()->pdo->createCommand('SELECT `id` FROM torrents ORDER BY added_at DESC LIMIT 50;')->queryColumn();

        $torrents = array_map(function ($id) {
            return app()->site->getTorrent($id);
        }, $fetch);

        return $this->render('rss_feed', ['torrents' => $torrents]);
    }
}
