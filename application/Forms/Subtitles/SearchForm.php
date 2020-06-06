<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:05 AM
 */

declare(strict_types=1);

namespace App\Forms\Subtitles;

use App\Forms\Traits\PaginationTrait;
use App\Libraries\Constant;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class SearchForm extends AbstractValidator
{
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 1, 'limit' => 50
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = [
            'page' => new Assert\PositiveOrZero(),
            'limit' => new AcmeAssert\RangeInt(['min' =>  0, 'max' => 100])
        ];

        if ($this->hasInput('tid')) {
            $rules['tid'] = new AcmeAssert\PositiveInt();
        } elseif ($this->hasInput('letter')) {
            $rules['letter'] = [new Assert\Length(1), new Assert\Type('alpha')];
        } elseif ($this->hasInput('search')) {
            $rules['search'] = new Assert\NotBlank();
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return [];
    }

    public function flush()
    {
        $pdo_where = [];
        if ($this->hasInput('tid')) {
            $pdo_where[] = ['AND torrent_id = :tid ', 'params' => ['tid' => $this->getInput('tid')]];
        } elseif ($this->hasInput('letter')) {
            $pdo_where[] = ['AND title LIKE :letter ', 'params' => ['letter' => "{$this->getInput('letter')}%"]];
        } elseif ($this->hasInput('search')) {
            $pdo_where[] = ['AND title LIKE :search ', 'params' => ['search' => "%{$this->getInput('search')}%"]];
        }

        $count = container()->get('pdo')->prepare([
            ['SELECT COUNT(`id`) FROM `subtitles` WHERE 1=1 '],
            ...$pdo_where
        ])->queryScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('pdo')->prepare([
            ['SELECT * FROM `subtitles` WHERE 1=1 '],
            ...$pdo_where,
            ['ORDER BY id DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $this->getPaginationOffset(), 'rows' => $this->getPaginationLimit()]],
        ])->queryAll();
        $this->setPaginationData($data);
    }

    // TODO remove is to site component
    public function getSubsSizeSum()
    {
        if (false === $size = container()->get('redis')->get(Constant::siteSubtitleSize)) {
            $size = container()->get('pdo')->prepare('SELECT SUM(`size`) FROM `subtitles`')->queryScalar();
            container()->get('redis')->set(Constant::siteSubtitleSize, $size);
        }
        return $size;
    }
}
