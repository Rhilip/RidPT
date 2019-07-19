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
        return $this->actionSearch();
    }

    public function actionSearch()
    {
        // FIXME use model to support SQL-search or elesticsearch
        // TODO add URI level Cache

        $limit = app()->request->get('limit',50);
        if (!filter_var($limit,FILTER_VALIDATE_INT) || $limit > 100) {
            $limit = 100;
        }
        $page = app()->request->get('page',1);
        if (!filter_var($page,FILTER_VALIDATE_INT)) {
            $page = 1;
        }

        $_tags = app()->request->get('tags');
        if ($_tags) {
            $tags = array_map('trim', explode(',', $_tags));
        } else {
            $tags = [];
        }

        $fetch = app()->pdo->createCommand([
            ['SELECT DISTINCT t.`id`, t.`added_at` FROM `torrents` t '],
            ['INNER JOIN map_torrents_tags mtt on t.id = mtt.torrent_id INNER JOIN tags t2 on mtt.tag_id = t2.id ', 'if' => count($tags)],
            ['WHERE 1=1 '],
            ['AND t2.tag IN(:tags) ', 'if' => count($tags), 'params' => ['tags' => $tags]],
            ['ORDER BY `t`.`added_at` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => ($page - 1) * $limit, 'rows' => $limit]],
        ])->queryColumn();

        $count = app()->pdo->createCommand([
            ['SELECT COUNT(t.`id`) FROM `torrents` t '],
            ['INNER JOIN map_torrents_tags mtt on t.id = mtt.torrent_id INNER JOIN tags t2 on mtt.tag_id = t2.id ', 'if' => count($tags)],
            ['WHERE 1=1 '],
            ['AND t2.tag IN(:tags) ', 'if' => count($tags), 'params' => ['tags' => $tags]],
        ])->queryScalar();

        $torrents = array_map(function ($id) {
            return new Torrent($id);
        }, $fetch);

        return $this->render('torrents/list', [
            'count' => $count,
            'limit' => $limit,
            'torrents' => $torrents
        ]);
    }

    public function actionTags()
    {
        $search = trim(app()->request->get('search', ''));
        $limit = app()->request->get('limit',50);
        if (!filter_var($limit,FILTER_VALIDATE_INT) || $limit > 100) {
            $limit = 100;
        }
        $page = app()->request->get('page',1);
        if (!filter_var($page,FILTER_VALIDATE_INT)) $page = 1;

        $tags = app()->pdo->createCommand([
            ['SELECT tags.*,COUNT(mtt.id) as `count` FROM tags LEFT JOIN map_torrents_tags mtt on tags.id = mtt.tag_id '],
            ['WHERE `tags`.`tag` LIKE :tag', 'if' => !empty($search), 'params' => ['tag' => '%' . $search . '%']],
            ['GROUP BY tags.id ORDER BY `tags`.`pinned`,`count` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => ($page - 1) * $limit, 'rows' => $limit]],
        ])->queryAll();

        if (count($tags) == 1) {  // If this search tag is unique, just redirect to search page
            return app()->response->redirect('/torrents/search?tags=' . $search);
        }

        $tag_count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `tags`')->queryScalar(); // TODO use `site_status` tables to store those data

        return $this->render('torrents/tags', ['search' => $search, 'tags' => $tags, 'count' => $tag_count]);
    }
}
