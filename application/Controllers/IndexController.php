<?php

namespace App\Controllers;

use Rid\Http\Controller;
use Rid\Database\BasePDOConnection;
use Rid\Redis\BaseRedisConnection;

class IndexController extends Controller
{

    // 默认动作
    public function actionIndex(BaseRedisConnection $redis, BasePDOConnection $pdo)
    {
        // Get Last News from redis cache
        $news = $redis->get('Site:recent_news');
        if ($news === false) { // Get news from Database and cache it in redis
            $news = $pdo->prepare('SELECT * FROM news ORDER BY create_at DESC LIMIT :max')->bindParams([
                'max' => config('base.max_news_sum')
            ])->queryAll();
            $redis->set('Site:recent_news', $news, 86400);
        }

        // Get All Links from redis cache
        $links = $redis->get('Site:links');
        if ($links === false) {
            $links = $pdo->prepare("SELECT `name`,`title`,`url` FROM links WHERE `status` = 'enabled' ORDER BY id ASC")->queryAll();
            $redis->set('Site:links', $links, 86400);
        }

        return $this->render('index', ['news' => $news, 'links' => $links]);
    }
}
