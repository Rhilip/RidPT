<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 8:09 PM
 */

declare(strict_types=1);

namespace App\Controllers\Rss;

use App\Forms\Rss\FeedForm;
use Rid\Http\AbstractController;

class FeedController extends AbstractController
{
    public function index()
    {
        $feed = new FeedForm();
        $feed->setInput(container()->get('request')->query->all());
        if ($feed->validate()) {
            $feed->flush();
            container()->get('response')->headers->set('Content-Type', 'text/xml');
            return $this->render('rss/feed', ['feed' => $feed]);
        } else {
            return $this->render('action/fail', ['msg' => $feed->getError()]);
        }
    }
}
