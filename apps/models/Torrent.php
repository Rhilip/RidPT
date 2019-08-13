<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace apps\models;

use apps\libraries\Constant;

use Rid\Utils;
use Rid\Exceptions\NotFoundException;

class Torrent
{
    use Utils\AttributesImportUtils;
    use Utils\ClassValueCacheUtils;

    private $id = null;

    private $owner_id;
    private $info_hash;

    private $status;

    private $added_at;

    private $complete;
    private $incomplete;
    private $downloaded;
    private $comments;

    private $title;
    private $subtitle;
    private $category;
    private $descr;
    private $uplver;
    private $hr;

    private $nfo;

    private $tags;
    private $pinned_tags;

    private $torrent_name;
    private $torrent_type;
    private $torrent_size;
    private $torrent_structure;

    protected $comment_perpage = 10;

    const TORRENT_TYPE_SINGLE = 'single';
    const TORRENT_TYPE_MULTI = 'multi';

    public function __construct($id = null)
    {
        $this->loadTorrentContentById($id);
        if ($this->id == null) {
            throw new NotFoundException('Not Found');  // FIXME
        }
    }

    public function loadTorrentContentById($id)
    {
        $self = app()->redis->hGetAll(Constant::torrentContent($id));
        if (empty($self)) {
            $self = app()->pdo->createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
                    "id" => $id
                ])->queryOne() ?? [];
            app()->redis->hMset(Constant::torrentContent($id), $self);
            app()->redis->expire(Constant::torrentContent($id), 1800);
        }
        $this->importAttributes($self);
    }

    /**
     * @return mixed
     */
    public function getTorrentStructure()
    {
        return $this->torrent_structure;
    }

    protected function getCacheNameSpace(): string
    {
        return Constant::torrentContent($this->id);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getOwnerId()
    {
        return $this->owner_id;
    }

    public function getOwner()
    {
        return app()->site->getUser($this->owner_id);
    }

    /**
     * @return mixed
     */
    public function getComplete()
    {
        return $this->complete;
    }

    /**
     * @return mixed
     */
    public function getIncomplete()
    {
        return $this->incomplete;
    }

    /**
     * @return mixed
     */
    public function getDownloaded()
    {
        return $this->downloaded;
    }

    /**
     * @return mixed
     */
    public function getAddedAt()
    {
        return $this->added_at;
    }

    /**
     * @return mixed
     */
    public function getTorrentName()
    {
        return $this->torrent_name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @return mixed
     */
    public function getDescr()
    {
        return $this->descr;
    }

    /**
     * @param bool $raw
     * @return mixed
     */
    public function getInfoHash($raw = false)
    {
        if ($raw) {
            return $this->info_hash;
        } else {
            return bin2hex($this->info_hash);
        }
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTorrentType()
    {
        return $this->torrent_type;
    }

    /**
     * @return mixed
     */
    public function getTorrentSize()
    {
        return $this->torrent_size;
    }

    /**
     * @return mixed
     */
    public function getUplver()
    {
        return $this->uplver;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return app()->site::CategoryDetail($this->category);
    }

    /**
     * @return array like [<tag1>, <tag2>, <tag3>]
     */
    public function getTags(): array
    {
        if (is_string($this->tags)) $this->tags = json_decode($this->tags, true);
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
            if (in_array($tag_name, $tags)) $pinned_tags[$tag_name] = $tag_class;
        }
        return $pinned_tags;
    }

    public function hasNfo()
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
        if ($convert) return self::nfoConvert($this->nfo, $swedishmagic);
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
                $s); // ?
        }
        return $s;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getLastCommentsDetails()
    {
        return $this->getCacheValue('last_comments_details', function () {
            $offset = $this->comments / $this->comment_perpage;
            return app()->pdo->createCommand('SELECT * FROM torrent_comments WHERE torrent_id = :tid LIMIT :o, :l;')->bindParams([
                'tid' => $this->id, 'o' => intval($offset), 'l' => $this->comment_perpage
            ])->queryAll();
        });
    }

}
