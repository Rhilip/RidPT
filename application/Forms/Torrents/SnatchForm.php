<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 9:20 AM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class SnatchForm extends AbstractValidator
{
    use isValidTorrentTrait;
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 1, 'limit' => 50
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'page' => new Assert\PositiveOrZero(),
            'limit' => new AcmeAssert\RangeInt(['min' =>  0, 'max' => 100]),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent'];
    }

    public function flush(): void
    {
        $pdo_where = [
            ['AND `torrent_id` = :tid ', 'params' => ['tid' => $this->getTorrentId()]]
        ];

        $count = container()->get('dbal')->prepare([
            ['SELECT COUNT(`id`) FROM `snatched` WHERE 1=1'],
            ...$pdo_where
        ])->fetchScalar();
        $this->setPaginationTotal($count);

        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('dbal')->prepare([
            ['SELECT * FROM `snatched` WHERE 1=1'],
            ...$pdo_where,
            ['ORDER BY finish_at, create_at DESC '],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->getPaginationOffset(), 'limit' => $this->getPaginationLimit()]]
        ])->fetchAll();
        $this->setPaginationData($data);
    }

    public function getTorrentId():int
    {
        return (int)$this->getInput('id');
    }
}
