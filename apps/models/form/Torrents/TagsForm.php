<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 8:52 PM
 */

namespace apps\models\form\Torrents;


use Rid\Validators\Pager;

class TagsForm extends Pager
{
    public $search;

    public static $MAX_LIMIT = 100;

    public function getRemoteTotal(): int
    {
        return app()->pdo->createCommand([
            ['SELECT COUNT(tags.id) as `count` FROM tags'],
            ['WHERE `tags`.`tag` LIKE :tag', 'if' => !empty($search), 'params' => ['tag' => '%' . $this->getData('search') . '%']],
        ])->queryScalar();
    }

    public function getRemoteData(): array
    {
        $search = $this->search;
        return app()->pdo->createCommand([
            ['SELECT tags.*,COUNT(mtt.id) as `count` FROM tags LEFT JOIN map_torrents_tags mtt on tags.id = mtt.tag_id '],
            ['WHERE `tags`.`tag` LIKE :tag', 'if' => !empty($search), 'params' => ['tag' => '%' . $search . '%']],
            ['GROUP BY tags.id ORDER BY `tags`.`pinned`,`count` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]],
        ])->queryAll();
    }

}
