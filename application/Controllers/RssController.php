<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 9:14
 */

namespace App\Controllers;

use App\Models\Form\Rss\FeedForm;

use Rid\Http\Controller;

class RssController extends Controller
{
    public function actionIndex()
    {
        $feed = new FeedForm();
        if (false === $feed->validate()) {
            return $this->render('action/fail', ['msg' => $feed->getError()]);
        }

        app()->response->headers->set('Content-Type', 'text/xml');
        return $this->render('rss/feed', ['feed' => $feed]);
    }

    public function actionGenerate()
    {
        // TODO
    }
}
