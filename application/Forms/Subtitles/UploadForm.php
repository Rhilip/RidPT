<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 11:09 PM
 */

declare(strict_types=1);

namespace App\Forms\Subtitles;

use App\Forms\Traits\isValidTorrentTrait;
use App\Libraries\Constant;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadForm extends AbstractValidator
{
    use isValidTorrentTrait;

    const SubtitleExtension = ['sub', 'srt', 'zip', 'rar', 'ace', 'txt', 'ssa', 'ass', 'cue'];

    private ?string $hashs;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'tid' => new AcmeAssert\PositiveInt(),
            'file' => new AcmeAssert\File([
                'maxSize' => config('upload.max_subtitle_file_size'),
                'extensions' => self::SubtitleExtension
            ]),
            'anonymous' => new Assert\Optional(new Assert\IsTrue()),
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isExistTorrent'];
    }

    public function flush(): void
    {
        $file = $this->getInput('file');
        $title = $this->getInput('title') ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        container()->get('dbal')->beginTransaction();
        try {
            $ext = $file->getClientOriginalExtension();
            container()->get('dbal')->prepare('INSERT INTO `subtitles`(`torrent_id`, `hashs` ,`title`, `filename`, `added_at`, `size`, `uppd_by`, `anonymous`, `ext`)
VALUES (:tid, :hashs, :title, :filename, NOW(), :size, :upper, :anonymous, :ext)')->bindParams([
                'tid' => $this->getTorrentId(), 'hashs' => $this->hashs,
                'title' => $title, 'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(), 'upper' => container()->get('auth')->getCurUser()->getId(),
                'anonymous' => $this->getInput('anonymous', 0), 'ext' => $ext
            ])->execute();
            $id = container()->get('dbal')->getLastInsertId();
            $file_log = container()->get('path.storage.subs') . DIRECTORY_SEPARATOR . $id . '.' . $ext;
            $file->move($file_log);
            container()->get('dbal')->commit();
        } catch (\Exception $e) {
            if (isset($file_loc)) {
                unlink($file_loc);
            }
            container()->get('dbal')->rollback();
            throw $e;
        }
        container()->get('redis')->del(Constant::siteSubtitleSize);
    }

    /** @noinspection PhpUnused */
    protected function checkSubtitleUniqueByHash()
    {
        /** @var UploadedFile $file */
        $file = $this->getInput('file');
        $this->hashs = $file_md5 = md5_file($file->getPathname());

        $exist_id = container()->get('dbal')->prepare('SELECT id FROM `subtitles` WHERE `hashs` = :hashs LIMIT 1;')->bindParams([
            'hashs' => $file_md5
        ])->fetchOne();

        if ($exist_id !== false) {
            $this->buildCallbackFailMsg('file', 'This Subtitle has been upload before.');
        }
    }


    public function getTorrentId(): int
    {
        return (int)$this->getInput('tid');
    }
}
