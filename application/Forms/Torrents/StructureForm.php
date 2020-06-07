<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 8:16 AM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use Rhilip\Bencode\Bencode;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class StructureForm extends AbstractValidator
{
    use isValidTorrentTrait;

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
    }

    public function getTorrentFileContentDict()
    {
        $file_loc = container()->get('path.storage.torrents') . DIRECTORY_SEPARATOR . $this->getTorrentId() . '.torrent';
        return Bencode::load($file_loc);
    }

    public function getTorrentId(): int
    {
        return (int)$this->getInput('id');
    }
}
