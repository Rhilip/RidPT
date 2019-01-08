<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace apps\models;

use Mix\Exceptions\NotFoundException;

class Torrent
{
    private $id;
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

    public function __construct($id = null)
    {
        $fetch = app()->pdo->createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $id
        ])->queryOne();
        if ($fetch) {
            foreach ($fetch as $key => $value)
                $this->$key = $value;
        } else {
            throw new NotFoundException("Not Found");
        }
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

    /**
     * @return mixed
     */
    public function getInfoHash()
    {
        return $this->info_hash;
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
     * if $type is "list" (Or other value not `tree`), we will return the default list like:
     *
     * [
     *    ["filename" => "f1/f2.text" , "size" => 1234],
     *    ["filename" => "f1/f3.text" , "size" => 1234],
     * ]
     *
     * @return array|bool|string
     */
    public function getFileList()
    {
        $list = app()->redis->get("TORRENT:" . $this->id . ":file_list");
        if ($list === false) {
            $list = app()->pdo->createCommand("SELECT `filename`,`size` FROM `files` WHERE `torrent_id` = :tid ORDER BY `filename` ASC;")->bindParams([
                "tid" => $this->id
            ])->queryAll();
            app()->redis->set("TORRENT:" . $this->id . ":file_list", $list);
        }
        return $list;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
}
