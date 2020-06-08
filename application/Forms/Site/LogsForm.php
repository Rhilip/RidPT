<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 10:58 PM
 */

declare(strict_types=1);

namespace App\Forms\Site;

use App\Enums\Site\LogLevel;
use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class LogsForm extends AbstractValidator
{
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'level' => 'all',
            'page' => 1, 'limit' => 100
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'page' => new Assert\PositiveOrZero(),
            'limit' => new AcmeAssert\RangeInt(['min' =>  0, 'max' => 200]),
            'search' => new Assert\Optional(new Assert\NotBlank()),
            'level' => new Assert\Choice(['all'] + LogLevel::values())
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush(): void
    {
        $where_pdo = [
            ['AND `level` IN (:level) ', 'params' => ['level' => $this->getLevels()]],
        ];
        if ($this->hasInput('search')) {
            $search = $this->getInput('search');
            $where_pdo[] = ['AND `msg` LIKE :search ', 'if' => strlen($search), 'params' => ['search' => "%$search%"]];
        }

        $count = container()->get('dbal')->prepare([
            ['SELECT COUNT(*) FROM `site_log` WHERE 1=1 '],
            ...$where_pdo
        ])->fetchScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('dbal')->prepare([
            ['SELECT * FROM `site_log` WHERE 1=1 '],
            ...$where_pdo,
            ['ORDER BY create_at DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->getPaginationOffset(), 'rows' => $this->getPaginationLimit()]],
        ])->fetchAll();
        $this->setPaginationData($data);
    }

    private function getLevels()
    {
        $input_level = $this->getInput('level');
        if ('all' == $input_level) {
            $levels = ['normal'];
            if (container()->get('auth')->getCurUser()->isPrivilege('see_site_log_mod')) {
                $levels[] = 'mod';
            }
            if (container()->get('auth')->getCurUser()->isPrivilege('see_site_log_leader')) {
                $levels[] = 'leader';
            }
        } else {
            $levels = [$input_level];
        }
        return $levels;
    }
}
