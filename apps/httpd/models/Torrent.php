<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace apps\httpd\models;


use Mix\Facades\PDO;

class Torrent
{
    private $id;
    private $owner_id;
    public $info_hash;

    public $status;

    private $added_at;

    private $complete;
    private $incomplete;
    private $downloaded;

    public $torrent_name;
    public $torrent_type;
    public $torrent_size;
    public $descr;
    public $uplver;

    public function __construct($id = null)
    {
        $fetch = PDO::createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $id
        ])->queryOne();
        if ($fetch) {
            foreach ($fetch as $key => $value)
                $this->$key = $value;
        }
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


    public static function TorrentFileLoc($id = 0) {
        return app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $id . ".torrent";
    }
}
