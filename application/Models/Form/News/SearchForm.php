<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 7:24 PM
 */

namespace App\Models\Form\News;

use Rid\Validators\Pagination;

/**
 * Class SearchForm
 * @package App\Models\Form\News
 * @property-read string $search The search Key
 * @property-read string $field The search Field ( title, body , or both? )
 */
class SearchForm extends Pagination
{
    public static function defaultData(): array
    {
        return [
            'search' => '',
            'field' => 'title',
            'page' => static::getDefaultPage(),
            'limit' => static::getDefaultLimit()
        ];
    }

    public static function inputRules(): array
    {
        return [
            'page' => 'Integer', 'limit' => 'Integer',
            'search' => 'AlphaNumHyphen',
            'field' => [
                ['RequiredWith', ['item' => 'search']],
                ['InList', ['list' => ['title', 'body', 'both']]]
            ]
        ];
    }

    public function getRemoteTotal(): int
    {
        $search = $this->search;
        $field = $this->field;
        if (empty($search)) {
            $count = app()->pdo->prepare('SELECT COUNT(*) FROM `news`;')->queryScalar();
        } else {
            $count = app()->pdo->prepare([
                ['SELECT COUNT(*) FROM `news` WHERE 1=1 '],
                ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($field == 'title' && !empty($search))],
                ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($field == 'body' && !empty($search))],
                ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($field == 'both' && !empty($search))],
            ])->queryScalar();
        }
        return $count;
    }

    public function getRemoteData(): array
    {
        $search = $this->search;
        $field = $this->field;

        return app()->pdo->prepare([
            ['SELECT * FROM `news` WHERE 1=1 '],
            ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($field == 'title' && !empty($search))],
            ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"], 'if' => ($field == 'body' && !empty($search))],
            ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%",'sb' => "%$search%"], 'if' => ($field == 'both' && !empty($search))],
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->offset, 'rows' => $this->limit]],
        ])->queryAll();
    }
}
