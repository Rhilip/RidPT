<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace App\Entity\Torrent;

use App\Entity\User\User;
use App\Enums\Torrent\Status;
use App\Libraries\Constant;

use Rid\Utils\Traits\ClassValueCache;

class Torrent
{
    use ClassValueCache;

    //-- Torrent Base Info --//
    protected int $id;
    protected int $owner_id;
    protected string $info_hash;
    protected string $status = Status::PENDING;
    protected string $added_at;

    protected int $complete = 0;
    protected int $incomplete = 0;
    protected int $downloaded = 0;
    protected int $comments = 0;

    protected string $title = '';
    protected string $subtitle = '';
    protected int $category = 0;
    protected string $descr = '';
    protected bool $uplver = false;
    protected bool $hr = false;
    protected $tags;

    protected int $team = 0;
    protected int $quality_audio = 0;
    protected int $quality_codec = 0;
    protected int $quality_medium = 0;
    protected int $quality_resolution = 0;

    protected int $torrent_size;

    //-- Torrent Extend Info --//
    protected string $torrent_name;
    protected string $torrent_type;
    protected ?bool $has_nfo;
    protected ?string $torrent_structure;


    protected $comment_perpage = 10;  // FIXME

    public function __construct($config)
    {
        $this->importAttributes($config);
    }

    // FIXME
    protected function importAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }

    protected function getCacheNameSpace(): string
    {
        return Constant::torrentContent($this->id);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function getOwner(): User
    {
        return container()->get(\App\Entity\User\UserFactory::class)->getUserById($this->owner_id);
    }

    public function getInfoHash($hex = true): string
    {
        return $hex ? bin2hex($this->info_hash) : $this->info_hash;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAddedAt(): string
    {
        return $this->added_at;
    }

    public function getComplete(): int
    {
        return $this->complete;
    }

    public function getIncomplete(): int
    {
        return $this->incomplete;
    }

    public function getDownloaded(): int
    {
        return $this->downloaded;
    }

    public function getComments(): int
    {
        return $this->comments;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubtitle(): string
    {
        return $this->subtitle;
    }

    public function getCategoryId(): int
    {
        return $this->category;
    }

    public function getCategory()
    {
        return container()->get('site')->CategoryDetail($this->category);
    }

    public function getTorrentSize(): int
    {
        return $this->torrent_size;
    }

    public function getTeamId()
    {
        return $this->team;
    }

    public function getTeam()
    {
        if ($this->team == 0) {
            return false;
        }
        return container()->get('site')->ruleTeam()[$this->team];
    }

    public function getQualityId(string $quality): int
    {
        return $this->{'quality_' . $quality} ?? 0;
    }

    public function getQuality(string $quality)
    {
        if ($this->getQualityId($quality) == 0) {
            return false;
        }
        return container()->get('site')->ruleQuality($quality)[$this->getQualityId($quality)];
    }

    public function getDescr(): string
    {
        return $this->descr;
    }

    /**
     * @return array like [<tag1>, <tag2>, <tag3>]
     */
    public function getTags(): array
    {
        if (is_string($this->tags)) {
            $this->tags = json_decode($this->tags, true);
        }
        return $this->tags ?? [];
    }

    /**
     * @return array like [<tag1> => <tag1_class_name>, <tag2> => <tag2_class_name>]
     */
    public function getPinnedTags(): array
    {
        $pinned_tags = [];
        $tags = $this->getTags();
        $rule_pinned_tags = container()->get('site')->rulePinnedTags();
        foreach ($rule_pinned_tags as $tag_name => $tag_class) {
            if (in_array($tag_name, $tags)) {
                $pinned_tags[$tag_name] = $tag_class;
            }
        }
        return $pinned_tags;
    }

    public function getUplver(): bool
    {
        return (boolean)$this->uplver;
    }

    public function getHr(): bool
    {
        return (boolean)$this->hr;
    }

    public function getTorrentName(): string
    {
        return $this->torrent_name;
    }

    public function getTorrentType(): string
    {
        return $this->torrent_type;
    }

    public function getTorrentStructure(): array
    {
        return json_decode($this->torrent_structure, true);
    }

    public function hasNfo(): bool
    {
        return (boolean)$this->has_nfo;
    }

    // FIXME It shouldn't be part of class Torrent
    public function getLastCommentsDetails()
    {
        return $this->getCacheValue('last_comments_details', function () {
            $offset = $this->comments / $this->comment_perpage;
            return container()->get('dbal')->prepare('SELECT * FROM torrent_comments WHERE torrent_id = :tid LIMIT :o, :l;')->bindParams([
                'tid' => $this->id, 'o' => (int)$offset, 'l' => $this->comment_perpage
            ])->fetchAll();
        });
    }
}
