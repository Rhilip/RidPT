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

class TorrentsController extends Controller
{

    public function actionIndex()
    {
        // TODO add pagination support
        $fetch = app()->pdo->createCommand('SELECT `id` FROM torrents ORDER BY added_at DESC LIMIT 50;')->queryColumn();

        $torrents = array_map(function ($id) {
            return new Torrent($id);
        }, $fetch);

        return $this->render('torrents/list', [
            'torrents' => $torrents
        ]);

    }

    public function actionSearch()
    {

    }
}
