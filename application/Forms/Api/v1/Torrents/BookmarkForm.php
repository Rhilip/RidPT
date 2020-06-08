<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 11:09 PM
 */

declare(strict_types=1);

namespace App\Forms\Api\v1\Torrents;

use App\Forms\Traits\isValidTorrentTrait;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class BookmarkForm extends AbstractValidator
{
    use isValidTorrentTrait;

    const DELETE = 'delete';
    const ADDED = 'added';

    // FIXME it don't support i18n
    const MESSAGE = [
        self::DELETE => 'Delete Old Bookmark Success',
        self::ADDED => 'Add New Bookmark Success'
    ];

    private ?string $status;

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
        $bookmark_exist = container()->get('dbal')->prepare('SELECT `id` FROM `bookmarks` WHERE `uid` = :uid AND `tid` = :tid ')->bindParams([
            'uid' => container()->get('auth')->getCurUser()->getId(),
            'tid' => $this->getTorrentId()
        ])->fetchScalar() ?: 0;
        if ($bookmark_exist > 0) {  // Delete the exist record
            container()->get('dbal')->prepare('DELETE FROM `bookmarks` WHERE `id` = :bid')->bindParams([
                'bid' => $bookmark_exist
            ])->execute();

            $this->status = self::DELETE;
        } else {  // Add new record
            container()->get('dbal')->prepare('INSERT INTO `bookmarks` (`uid`, `tid`) VALUES (:uid, :tid)')->bindParams([
                'uid' => container()->get('auth')->getCurUser()->getId(),
                'tid' => $this->getInput('id')
            ])->execute();

            $this->status = self::ADDED;
        }

        // Update User Bookmark
        container()->get('auth')->getCurUser()->updateBookmarkList();
    }

    public function getTorrentId(): int
    {
        return (int)$this->getInput('id');
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getStatusMessage()
    {
        return self::MESSAGE[$this->getStatus()];
    }
}
