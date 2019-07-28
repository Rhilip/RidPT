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
use Rid\Utils\ClassValueCacheUtils;

class Torrent
{
    use AttributesImportUtils, ClassValueCacheUtils;

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

    /** @var array */
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
            app()->redis->expire('Torrent:' . $id . ':base_content', 900);
        }
        $this->importAttributes($self);
    }

    public static function TorrentFileLoc($id = 0)
    {
        return app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $id . ".torrent";
    }

    protected function getCacheNameSpace(): string
    {
        return 'Torrent:' . $this->id . ':base_content';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /** FIXME
     * @return mixed
     */
    public function getOwnerId()
    {
        if ($this->getUplver() == 'yes' and !app()->user->isPrivilege('see_anonymous_uploader')) {
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
         * @see http://www.bittorrent.org/beps/bep_0012.html
         * @see https://web.archive.org/web/20190724110959/https://blog.rhilip.info/archives/1108/
         *      which discuss about multitracker behaviour on common bittorrent client ( Chinese Version )
         */
        if ($multi_trackers = config("base.site_multi_tracker_url")) {
            // Add our main tracker into multi_tracker_list to avoid lost....
            $multi_trackers = config("base.site_tracker_url") . "," . $multi_trackers;
            $multi_trackers_list = explode(",", $multi_trackers);
            $multi_trackers_list = array_unique($multi_trackers_list);  // use array_unique to remove dupe tracker
            // fulfill each tracker with scheme and suffix about user identity
            $multi_trackers_list = array_map(function ($uri) use ($scheme, $announce_suffix) {
                return $scheme . $uri . $announce_suffix;
            }, $multi_trackers_list);

            if (config('base.site_multi_tracker_behaviour') == 'separate') {
                /** d['announce-list'] = [ [tracker1], [backup1], [backup2] ] */
                foreach ($multi_trackers_list as $tracker) {  // separate each tracker to different tier
                    $dict["announce-list"][] = [$tracker];  // Make each tracker as tier
                }
            } else {  // config('base.site_multi_tracker_behaviour') ==  'union'
                /** d['announce-list'] = [[ tracker1, tracker2, tracker3 ]] */
                $dict["announce-list"][] = $multi_trackers_list;
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
        return (new Category())->setId($this->category);  // FIXME if will call cache every time for each torrent
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->getCacheValue('tags', function () {
            return app()->pdo->createCommand('
                SELECT tag, class_name, pinned FROM tags 
                  INNER JOIN map_torrents_tags mtt on tags.id = mtt.tag_id 
                  INNER JOIN torrents t on mtt.torrent_id = t.id 
                WHERE t.id = :tid ORDER BY tags.pinned DESC')->bindParams([
                'tid' => $this->id
            ])->queryAll();
        });
    }

    /**
     * @return array
     */
    public function getPinnedTags(): array
    {
        return $this->getCacheValue('pinned_tags', function () {
            return array_filter($this->getTags(), function ($tag) {
                return $tag['pinned'] == 1;
            });
        });
    }

    public function hasNfo() {
        return (boolean) $this->nfo;
    }

    /**
     * @return mixed
     */
    public function getNfo()
    {
        return $this->nfo;
    }

    public function getComments() {
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
