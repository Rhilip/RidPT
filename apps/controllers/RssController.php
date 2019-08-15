<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 9:14
 */

namespace apps\controllers;

use apps\models\form\Rss\FeedForm;

use Rid\Http\Controller;

class RssController extends Controller
{
    public function actionIndex()
    {
        // FIXME add torrent search
        $feed = new FeedForm();
        if (false === $feed->validate()) {
            return $this->render('action/action_fail', ['msg' => $feed->getError()]);
        }

        app()->response->setHeader('Content-Type', 'text/xml');
        return $this->render('rss/feed', ['feed' => $feed]);
    }

    public function actionGenerate()
    {
        // TODO
    }
}
