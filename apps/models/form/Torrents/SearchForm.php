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

    /**
     * user input may '&tags=<tag1>,<tag2>' (string)
     *             or '&tags[]=<tag1>&tags[]=<tag2>' (array)
     * We deal those with an `AND` operation -> 'AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags))'
     *
     */
    private function getTagsArray()
    {
        if (is_null($this->_tags)) {
            $tags = $this->getInput('tags') ?? [];

            if (is_string($tags)) $tags = explode(',', $tags);
            $this->_tags = array_map('trim', $tags);
        }

        return $this->_tags;
    }

    protected function getRemoteTotal(): int
    {
        $tags = $this->getTagsArray();

        return app()->pdo->createCommand([
            ['SELECT COUNT(`id`) FROM `torrents` WHERE 1=1 '],
            ['AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags))', 'if' => count($tags), 'params' => ['tags' => $tags]],
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        $tags = $this->getTagsArray();

        $fetch = app()->pdo->createCommand([
            ['SELECT `id`, `added_at` FROM `torrents` WHERE 1=1 '],
            ['AND JSON_CONTAINS(`tags`, JSON_ARRAY(:tags))', 'if' => count($tags), 'params' => ['tags' => $tags]],
            ['ORDER BY `added_at` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]],
        ])->queryColumn();

        return array_map(function ($id) {
            return app()->site->getTorrent($id);
        }, $fetch);
    }
}
