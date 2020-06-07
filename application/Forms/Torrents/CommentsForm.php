<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 9:46 AM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use App\Forms\Traits\PaginationTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class CommentsForm extends AbstractValidator
{
    use isValidTorrentTrait;
    use PaginationTrait;

    public function __construct()
    {
        $this->setInput([
            'page' => 1, 'limit' => 20
        ]);
    }

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'page' => new Assert\PositiveOrZero(),
            'limit' => new AcmeAssert\RangeInt(['min' =>  0, 'max' => 50])
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent'];
    }

    public function flush(): void
    {
        $this->setPaginationTotal($this->getTorrent()->getComments());
        $this->setPaginationLimit($this->getInput('limit'));
        $this->setPaginationPage($this->getInput('page'));

        $data = container()->get('pdo')->prepare([
            ['SELECT * FROM `torrent_comments` WHERE torrent_id = :tid', 'params' => ['tid' => $this->getTorrentId()]],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->getPaginationOffset(), 'limit' => $this->getPaginationLimit()]]
        ])->queryAll();
        $this->setPaginationData($data);
    }

    public function getTorrentId(): int
    {
        return (int)$this->getInput('id');
    }
}
