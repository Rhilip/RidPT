<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 10:31 PM
 */

declare(strict_types=1);

namespace App\Forms\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use Rid\Validators\AbstractValidator;

use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class DetailsForm extends AbstractValidator
{
    use isValidTorrentTrait;


    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
            'hit' => new Assert\Optional(new Assert\IsTrue())
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent'];
    }

    public function flush()
    {
        if ($this->getInput('hit')) {
            // TODO
        }
    }

    public function getTorrentId():int
    {
        return (int)$this->getInput('id');
    }
}
