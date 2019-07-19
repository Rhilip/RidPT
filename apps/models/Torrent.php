<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace apps\models;

use Rid\Bencode\Bencode;
use Rid\Exceptions\NotFoundException;
use Rid\Utils\AttributesImportUtils;

class Torrent
{
    use AttributesImportUtils;

    private $id = null;

    private $owner_id;
    private $info_hash;

    private $status;

    private $added_at;

    private $complete;
    private $incomplete;
    private $downloaded;

    private $title;
    private $subtitle;
    private $category;
    private $descr;
    private $uplver;

    /** @var array */
    private $tags;

    private $torrent_name;
    private $torrent_type;
    private $torrent_size;
    private $torrent_structure;

    const TORRENT_TYPE_SINGLE = 'single';
    const TORRENT_TYPE_MULTI = 'multi';

    public function __construct($id = null)
    {
        $this->loadTorrentContentById($id);
        if ($this->id == null) {
            throw new NotFoundException("Not Found");
        }
    }

    public function loadTorrentContentById($id)
    {
        $self = app()->redis->hGetAll('Torrent:' . $id . ':base_content');
        if (empty($self)) {
            $self = app()->pdo->createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
                    "id" => $id
                ])->queryOne() ?? [];
            app()->redis->hMset('Torrent:' . $id . ':base_content', $self);
            app()->redis->expire('Torrent:' . $id . ':base_content', 10 * 60);
        }
        $this->importAttributes($self);
    }

    public static function TorrentFileLoc($id = 0)
    {
        return app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $id . ".torrent";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getOwnerId()
    {
        if ($this->getUplver() == 'yes' and app()->user->getClass(true) < config('authority.see_anonymous_uploader')) {
            return 0;
        } else {
            return $this->owner_id;
        }
    }

    public function getOwner()
    {
        return new User($this->owner_id);
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

    public function getRawDict()
    {
        $file = self::TorrentFileLoc($this->id);
        $dict = Bencode::load($file);
        return $dict;
    }

    public function getDownloadDict($encode = true)
    {
        $dict = $this->getRawDict();

        $scheme = "http://";
        if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN))
            $scheme = "https://";
        else if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
            $scheme = "http://";
        else if (app()->request->isSecure())
            $scheme = "https://";

        $announce_suffix = "/announce?passkey=" . app()->user->getPasskey();
        $dict["announce"] = $scheme . config("base.site_tracker_url") . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * See more on : http://www.bittorrent.org/beps/bep_0012.html
         */
        if ($muti_tracker = config("base.site_muti_tracker_url")) {
            $dict["announce-list"] = [];

            // Add our main tracker into muti_tracker_list to avoid lost error....
            $muti_tracker = config("base.site_tracker_url") . "," . $muti_tracker;

            $muti_tracker_list = explode(",", $muti_tracker);
            foreach (array_unique($muti_tracker_list) as $tracker) {  // use array_unique to remove dupe tracker
                $dict["announce-list"][] = [$scheme . $tracker . $announce_suffix];
            }
        }

        return $encode ? Bencode::encode($dict) : $dict;
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
        return (new Category())->setId($this->category);
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        if (is_null($this->tags)) {
            $this->tags = app()->pdo->createCommand('
                SELECT tag, class_name, pinned FROM tags 
                  INNER JOIN map_torrents_tags mtt on tags.id = mtt.tag_id 
                  INNER JOIN torrents t on mtt.torrent_id = t.id 
                WHERE t.id = :tid ORDER BY tags.pinned DESC')->bindParams([
                'tid' => $this->id
            ])->queryAll();
            app()->redis->hSet('Torrent:' . $this->id . ':base_content', 'tags', $this->tags);
        }

        return $this->tags;
    }
}
