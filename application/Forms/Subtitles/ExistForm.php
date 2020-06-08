<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:32 AM
 */

declare(strict_types=1);

namespace App\Forms\Subtitles;

use Rid\Validators\AbstractValidator;

abstract class ExistForm extends AbstractValidator
{
    private ?array $subtitle;

    abstract public function getSubtitleId(): int;

    /** @noinspection PhpUnused */
    protected function isValidSubtitle()
    {
        $this->subtitle = container()->get('dbal')->prepare('SELECT * FROM `subtitles` WHERE id = :sid LIMIT 1;')->bindParams([
            'sid' => $this->getSubtitleId()
        ])->fetchOne();

        if ($this->subtitle === false) {
            $this->buildCallbackFailMsg('file', 'File not found');
        }
    }

    /**
     * @return array|null
     */
    public function getSubtitle(): ?array
    {
        return $this->subtitle;
    }

    public function getSubtitleLoc()
    {
        $filename = $this->subtitle['id'] . '.' . $this->subtitle['ext'];
        return container()->get('path.storage.subs') . DIRECTORY_SEPARATOR . $filename;
    }
}
