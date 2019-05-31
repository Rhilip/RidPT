<?php

namespace apps\controllers;

use Rid\Http\Controller;

class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        // Get Last News from redis cache
        $news = app()->redis->get('Site:recent_news');
        if (empty($news)) { // Get news from Database and cache it in redis
            $news = app()->pdo->createCommand('SELECT * FROM news ORDER BY create_at DESC LIMIT :max')->bindParams([
                'max' => app()->config->get('base.max_news_sum')
            ])->queryAll();
            app()->redis->set('Site:recent_news',$news,86400);
        }

        return $this->render('index',['news'=>$news]);
    }
}
