<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 9:10 PM
 */

namespace apps\models\form\Torrents;

use Rid\Validators\Pager;

class SearchForm extends Pager
{
    public $tags;

    public static $MAX_LIMIT = 100;

    private $_tags;

    private function getTagsArray()
    {
        if (is_null($this->_tags)) {
            $tags = $this->getData('tags');
            $this->_tags = $tags ? array_map('trim', explode(',', $tags)) : [];
        }

        return $this->_tags;
    }

    protected function getRemoteTotal(): int
    {
        $tags = $this->getTagsArray();

        return app()->pdo->createCommand([
            ['SELECT COUNT(t.`id`) FROM `torrents` t '],
            ['INNER JOIN map_torrents_tags mtt on t.id = mtt.torrent_id INNER JOIN tags t2 on mtt.tag_id = t2.id ', 'if' => count($tags)],
            ['WHERE 1=1 '],
            ['AND t2.tag IN(:tags) ', 'if' => count($tags), 'params' => ['tags' => $tags]],
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        $tags = $this->getTagsArray();

        $fetch = app()->pdo->createCommand([
            ['SELECT DISTINCT t.`id`, t.`added_at` FROM `torrents` t '],
            ['INNER JOIN map_torrents_tags mtt on t.id = mtt.torrent_id INNER JOIN tags t2 on mtt.tag_id = t2.id ', 'if' => count($tags)],
            ['WHERE 1=1 '],
            ['AND t2.tag IN(:tags) ', 'if' => count($tags), 'params' => ['tags' => $tags]],
            ['ORDER BY `t`.`added_at` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]],
        ])->queryColumn();

        return array_map(function ($id) {
            return app()->site->getTorrent($id);
        }, $fetch);
    }
}
