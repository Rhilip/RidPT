<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:34 AM
 */

declare(strict_types=1);

namespace App\Forms\Subtitles;

use App\Forms\Traits\sendFileTrait;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints as Assert;

class DownloadForm extends ExistForm
{
    use sendFileTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt(),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidSubtitle'];
    }

    public function flush(): void
    {
        $this->addDownloadHit();
    }

    private function addDownloadHit()
    {
        container()->get('pdo')->prepare('UPDATE `subtitles` SET `hits` = `hits` + 1 WHERE id = :sid')->bindParams([
            'sid' => $this->getInput('id')
        ])->execute();
    }

    public function getSubtitleId(): int
    {
        return (int)$this->getInput('id');
    }

    public function sendFileContentToClient()
    {
        container()->get('response')->setFile($this->getSubtitleLoc());
        container()->get('response')->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->getSubtitle()['filename']
        );
    }
}
