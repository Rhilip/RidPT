<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 10:58 PM
 */

declare(strict_types=1);

namespace App\Forms\Api\v1\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class FileListForm extends AbstractValidator
{
    use isValidTorrentTrait;

    private ?array $structure;


    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent'];
    }

    public function flush(): void
    {
        $structure = container()->get('dbal')->prepare('SELECT structure FROM torrent_structures WHERE tid = :tid')->bindParams([
            'tid' => $this->getTorrentId()
        ])->fetchScalar();
        $this->structure = json_decode($structure, true);
    }

    public function getTorrentId(): int
    {
        return (int)$this->getInput('id');
    }

    /**
     * @return array
     */
    public function getStructure(): array
    {
        return $this->structure;
    }
}
