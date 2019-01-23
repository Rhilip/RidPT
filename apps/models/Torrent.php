<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 10:10
 */

namespace apps\models;

use SandFoxMe\Bencode\Bencode;

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

    const TORRENT_TYPE_SINGLE = 'single';
    const TORRENT_TYPE_MULTI = 'multi';

    public function __construct($id = null)
    {
        # TODO Add redis hash type cache
        $fetch = app()->pdo->createCommand("SELECT * FROM `torrents` WHERE id=:id LIMIT 1;")->bindParams([
            "id" => $id
        ])->queryOne();
        if ($fetch) {
            # TODO Only allow admins see deleted torrents
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

    public function getRawDict() {
        $file = self::TorrentFileLoc($this->id);
        $dict = Bencode::load($file);
        return $dict;
    }

    public function getDownloadDict($encode = true) {
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
    public function getInfoHash($raw=false)
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
     * if $type is "list" (Or other value not `tree`), we will return the default list like:
     *
     * [
     *    ["filename" => "f1/f2.text" , "size" => 1234],
     *    ["filename" => "f1/f3.text" , "size" => 2234],
     * ]
     *
     * elseif $type is "tree" , the return array is like this when it's `single` torrent
     *
     * [
     *    "f1.text" => 1234
     * ]
     *
     * And will convert to `tree` like this when it's `multi` torrent by using the
     * private static function getFileTree($array, $delimiter = '/')
     *
     * [
     *    "f1" => [
     *        "f2.text" => 1234,
     *        "f3.text" => 2234
     *     ]
     * ]
     *
     * Each result will be cached in redis since it will never change.
     *
     * @param string $type enum("list","tree") The format of fileList
     * @return array|bool|string
     */
    public function getFileList($type = "list")
    {
        if (!in_array($type, ["list", "tree"]))
            $type = "list";

        $list = app()->redis->get("TORRENT:" . $this->id . ":file_list");
        if ($list === false) {
            $list = app()->pdo->createCommand("SELECT `filename`,`size` FROM `files` WHERE `torrent_id` = :tid ORDER BY `filename` ASC;")->bindParams([
                "tid" => $this->id
            ])->queryAll();
            app()->redis->set("TORRENT:" . $this->id . ":file_list", $list);
        }

        if ($type == "tree") {
            $tree = app()->redis->get("TORRENT:" . $this->id . ":file_list_tree");
            if ($tree === false) {
                $tree = array_column($list, 'size', 'filename');

                // Only when Torrent Type is "multi" , We need to use `getFileTree` to convert file list to tree
                if ($this->getTorrentType() == self::TORRENT_TYPE_MULTI) {
                    $tree = [$this->getTorrentName() => self::getFileTree($tree)];
                }

                app()->redis->set("TORRENT:" . $this->id . ":file_list_tree", $tree);
            }
            return $tree;
        }

        return $list;
    }

    private static function getFileTree($array, $delimiter = '/')
    {
        if (!is_array($array)) return array();

        $splitRE = '/' . preg_quote($delimiter, '/') . '/';
        $returnArr = array();
        foreach ($array as $key => $val) {
            // Get parent parts and the current leaf
            $parts = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
            $leafPart = array_pop($parts);

            // Build parent structure
            // Might be slow for really deep and large structures
            $parentArr = &$returnArr;
            foreach ($parts as $part) {
                if (!isset($parentArr[$part])) {
                    $parentArr[$part] = array();
                } elseif (!is_array($parentArr[$part])) {
                    $parentArr[$part] = array();
                }
                $parentArr = &$parentArr[$part];
            }

            // Add the final part to the structure
            if (empty($parentArr[$leafPart])) {
                $parentArr[$leafPart] = $val;
            }
        }
        return $returnArr;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }
}
