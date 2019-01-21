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

    const TORRENT_TYPE_SINGLE = 'single';
    const TORRENT_TYPE_MULTI = 'multi';

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
     *    ["filename" => "f1/f3.text" , "size" => 2234],
     * ]
     *
     * elseif $type is "tree" , the return array is like this by using the private static function getFileTree($array, $delimiter = '/')
     * [
     *    "f1" => [
     *        "f2.text" => 1234,
     *        "f3.text" => 2234
     *     ]
     * ]
     *
     * Each result will be cached since it will never change.
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
                /**
                 * Sort $list to this sample by using array_column()
                 * So that getFileTree() can use this Array()
                 *
                 * [
                 *    "f1/f2.text" => 1234,
                 *    "f1/f3.text" => 2234
                 * ]
                 */
                $list = array_column($list, 'size', 'filename');

                // Only when Torrent Type is "multi" , We need to use `getFileTree` to convert file list to tree
                if ($this->getTorrentType() == self::TORRENT_TYPE_MULTI) {
                    $tree = self::getFileTree($list);
                    $tree = [$this->getTorrentName() => $tree];
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
