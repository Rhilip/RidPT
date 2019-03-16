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
        return $this->owner_id;
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
        $userInfo = app()->session->get('userInfo');  // FIXME add remote download by &passkey=  (Add change our BeforeMiddle) or token ?

        $scheme = "http://";
        if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN))
            $scheme = "https://";
        else if (filter_var(app()->request->get("https"), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
            $scheme = "http://";
        else if (app()->request->isSecure())
            $scheme = "https://";

        // FIXME bad code
        $passkey = app()->pdo->createCommand("SELECT `passkey` FROM `users` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $userInfo["uid"]
        ])->queryScalar();

        $announce_suffix = "/announce?passkey=" . $passkey;
        $dict["announce"] = $scheme . app()->config->get("base.site_tracker_url") . $announce_suffix;

        /** BEP 0012 Multitracker Metadata Extension
         * See more on : http://www.bittorrent.org/beps/bep_0012.html
         */
        if ($muti_tracker = app()->config->get("base.site_muti_tracker_url")) {
            $dict["announce-list"] = [];

            // Add our main tracker into muti_tracker_list to avoid lost error....
            $muti_tracker = app()->config->get("base.site_tracker_url") . "," . $muti_tracker;

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

    public function getFileList($type = 'list')
    {
        if (!in_array($type, ['list', 'tree']))
            $type = 'list';

        if ($type == 'tree') {
            return json_decode($this->torrent_structure, true);
        } else {
            $list = app()->redis->get('TORRENT:' . $this->id . ':file_list');
            if ($list === false) {
                $list = app()->pdo->createCommand("SELECT `filename`,`size` FROM `files` WHERE `torrent_id` = :tid ORDER BY `filename` ASC;")->bindParams([
                    "tid" => $this->id
                ])->queryAll();
                app()->redis->set('TORRENT:' . $this->id . ':file_list', $list);
            }

            return $list;
        }
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
}
