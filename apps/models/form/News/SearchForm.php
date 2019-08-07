<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 7:24 PM
 */

namespace apps\models\form\News;


use Rid\Validators\Pager;

class SearchForm extends Pager
{

    public $query;
    public $search;

    public static function defaultData()
    {
        return [
            'query' => 'title',
            'search' => ''
        ];
    }

    public static function inputRules()
    {
        return [
            'page' => 'Integer', 'limit' => 'Integer',
            'query' => [
                ['RequiredWith', ['item' => 'search']],
                ['InList', ['list' => ['title', 'body', 'both']]]
            ],
            'search' => 'AlphaNumHyphen'
        ];
    }

    public function getRemoteTotal(): int
    {
        $search = $this->getData('search');
        $query = $this->getData('query');
        if (empty($search)) {
            $count = app()->pdo->createCommand('SELECT COUNT(*) FROM `news`;')->queryScalar();
        } else {
            $count = app()->pdo->createCommand([
                ['SELECT COUNT(*) FROM `news` WHERE 1=1 '],
                ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'title' && !empty($search))],
                ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'body' && !empty($search))],
                ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($query == 'both' && !empty($search))],
            ])->queryScalar();
        }
        return $count;
    }

    public function getRemoteData(): array
    {
        $search = $this->search;
        $query = $this->query;

        return app()->pdo->createCommand([
            ['SELECT * FROM `news` WHERE 1=1 '],
            ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'title' && !empty($search))],
            ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($query == 'body' && !empty($search))],
            ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($query == 'both' && !empty($search))],
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]],
        ])->queryAll();
    }
}
