<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 11:06 PM
 */

namespace apps\models\form\Traits;


use apps\models\Torrent;

trait isValidTorrentTrait
{
    public $torrent_id;
    public $tid;
    public $id;

    /** @var Torrent */
    protected $torrent;

    public static function callbackRules()
    {
        return ['isExistTorrent'];
    }

    /**
     * @return Torrent
     */
    public function getTorrent(): Torrent
    {
        return $this->torrent;
    }

    protected function isExistTorrent() {
        $tid = $this->getData('torrent_id') ?? $this->getData('tid') ?? $this->getData('id');
        $torrent_exist = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents` WHERE `id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
        if ($torrent_exist == 0) {
            $this->buildCallbackFailMsg('Torrent', 'The torrent id ('. $tid. ') is not exist in our database');
            return;
        }

        $this->torrent = app()->site->getTorrent($tid);
    }
}
