<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace App\Entity\Torrent;

use App\Entity\User\User;
use App\Libraries\Constant;

use Rid\Utils;
use Rid\Base\BaseObject;

class Torrent extends BaseObject
{
    use Utils\ClassValueCacheUtils;

    //-- Torrent Base Info --//
    protected int $id;
    protected int $owner_id;
    protected string $info_hash;
    protected string $status = TorrentStatus::PENDING;
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
    protected ?string $nfo;
    protected ?string $torrent_structure;


    protected $comment_perpage = 10;  // FIXME

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($config)
    {
        $this->importAttributes($config);
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
        return app()->site->getUser($this->owner_id);
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
        return app()->site->CategoryDetail($this->category);
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
        return app()->site->ruleTeam()[$this->team];
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
        return app()->site->ruleQuality($quality)[$this->getQualityId($quality)];
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
        $rule_pinned_tags = app()->site->rulePinnedTags();
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
        return (boolean)$this->nfo;
    }

    /**
     * @param bool $convert
     * @param bool $swedishmagic
     * @return mixed
     */
    public function getNfo($convert = true, $swedishmagic = false)
    {
        if ($convert) {
            return self::nfoConvert($this->nfo, $swedishmagic);
        }
        return $this->nfo;
    }

    /** Code for Viewing NFO file
     * @see https://github.com/zcqian/tjupt/blob/933f13be/include/functions.php#L4157-L4196
     *
     * @param $ibm_437
     * @param bool $swedishmagic
     * @return mixed|string|string[]|null
     */
    protected static function nfoConvert($ibm_437, $swedishmagic = false)
    {
        $cf = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 8962, 199, 252, 233, 226, 228, 224, 229, 231, 234, 235, 232, 239, 238, 236, 196, 197, 201, 230, 198, 244, 246, 242, 251, 249, 255, 214, 220, 162, 163, 165, 8359, 402, 225, 237, 243, 250, 241, 209, 170, 186, 191, 8976, 172, 189, 188, 161, 171, 187, 9617, 9618, 9619, 9474, 9508, 9569, 9570, 9558, 9557, 9571, 9553, 9559, 9565, 9564, 9563, 9488, 9492, 9524, 9516, 9500, 9472, 9532, 9566, 9567, 9562, 9556, 9577, 9574, 9568, 9552, 9580, 9575, 9576, 9572, 9573, 9561, 9560, 9554, 9555, 9579, 9578, 9496, 9484, 9608, 9604, 9612, 9616, 9600, 945, 223, 915, 960, 931, 963, 181, 964, 934, 920, 937, 948, 8734, 966, 949, 8745, 8801, 177, 8805, 8804, 8992, 8993, 247, 8776, 176, 8729, 183, 8730, 8319, 178, 9632, 160);
        $s = "";
        for ($c = 0; $c < strlen($ibm_437); $c++) {  // cyctle through the whole file doing a byte at a time.
            $byte = $ibm_437[$c];
            $ob = ord($byte);
            if ($ob >= 127) {  // is it in the normal ascii range
                $s .= '&#' . $cf[$ob] . ';';
            } else {
                $s .= $byte;
            }
        }

        if ($swedishmagic) {
            $s = str_replace(  // Code windows to dos
                ["\345", "\344", "\366", "\311", "\351"],   // ['å','ä','ö','É','é']
                ["\206", "\204", "\224", "\220", "\202"],   // ['','','','','']
                $s);

            $s = preg_replace(
                ["/([ -~])\305([ -~])/", "/([ -~])\304([ -~])/", "/([ -~])\326([ -~])/"],
                ["\\1\217\\2", "\\1\216\\2", "\\1\231\\2"],
                $s
            ); // ?
        }
        return $s;
    }


    public function getLastCommentsDetails()
    {
        return $this->getCacheValue('last_comments_details', function () {
            $offset = $this->comments / $this->comment_perpage;
            return app()->pdo->prepare('SELECT * FROM torrent_comments WHERE torrent_id = :tid LIMIT :o, :l;')->bindParams([
                'tid' => $this->id, 'o' => intval($offset), 'l' => $this->comment_perpage
            ])->queryAll();
        });
    }
}
