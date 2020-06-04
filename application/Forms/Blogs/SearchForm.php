<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 10:57 AM
 */

declare(strict_types=1);

namespace App\Forms\Blogs;

use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class SearchForm extends AbstractValidator
{
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'field' => 'title',
            'page' => 0, 'limit' => 10
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = [
            'page' => new Assert\PositiveOrZero(),
            'limit' => new Assert\Range(['min' => 0, 'max' => 50])
        ];
        if ($this->hasInput('search')) {
            $rules['search'] = new Assert\NotBlank();
            $rules['field'] = new Assert\Choice(['title', 'body', 'both']);
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush()
    {
        $search = $this->getInput('search');
        $field = $this->getInput('field');

        $where_pdo = [];
        if ($search) {
            if ($field == 'title') {
                $where_pdo[] = ['AND `title` LIKE :search ', 'params' => ['search' => "%$search%"]];
            } elseif ($field == 'body') {
                $where_pdo[] = ['AND `body` LIKE :search ', 'params' => ['search' => "%$search%"]];
            } elseif ($field == 'both') {
                $where_pdo[] = ['AND `title` LIKE :st OR `body` LIKE :sb ', 'params' => ['st' => "%$search%", 'sb' => "%$search%"]];
            }
        }

        $count = container()->get('pdo')->prepare([
            ['SELECT COUNT(*) FROM blogs WHERE 1=1 '],
            ...$where_pdo
        ])->queryScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('pdo')->prepare([
            ['SELECT * FROM blogs WHERE 1=1 '],
            ...$where_pdo,
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->getPaginationOffset(), 'rows' => $this->getPaginationLimit()]],
        ])->queryAll();
        $this->setPaginationData($data);
    }
}
