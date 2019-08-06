<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 16:53
 */

namespace apps\controllers;

use apps\models\Torrent;

use Rid\Http\Controller;

class TorrentController extends Controller
{
    public function actionDetails()
    {
        $tid = app()->request->get('id');
        $torrent = new Torrent($tid);

        return $this->render('torrent/details', ['torrent' => $torrent]);
    }

    public function actionEdit() // TODO
    {

    }

    public function actionSnatch()  // TODO
    {
        $tid = app()->request->get('id');
        $torrent = new Torrent($tid);

        return $this->render('torrent/snatch', ['torrent' => $torrent]);
    }

    public function actionDownload()
    {
        $tid = app()->request->get('id');

        // TODO add download rate limit

        $torrent = new Torrent($tid);  // TODO If torrent is not exist or can't visit , a notfound exception should throw out........
        $filename = '[' . config('base.site_name') . ']' . $torrent->getTorrentName() . '.torrent';

        app()->response->setHeader('Content-Type', 'application/x-bittorrent');
        if (strpos(app()->request->header('user-agent'), 'IE')) {
            app()->response->setHeader('Content-Disposition', 'attachment; filename=' . str_replace('+', '%20', rawurlencode($filename)));
        } else {
            app()->response->setHeader('Content-Disposition', "attachment; filename=\"$filename\" ; charset=utf-8");
        }

        return $torrent->getDownloadDict(true);
    }

    public function actionComments()
    {
        // TODO
    }

    public function actionStructure()
    {
        $tid = app()->request->get('id');

        $torrent = new Torrent($tid);  // If torrent is not exist or can't visit , a notfound exception will throw out........
        return $this->render('torrent/structure', ['torrent' => $torrent]);
    }
}
