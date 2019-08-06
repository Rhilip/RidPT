<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 5:20 PM
 */

namespace apps\models\form\Base;


use apps\models\Torrent;
use Rid\Validators\Validator;

class TorrentForm extends Validator
{

    public $id;
    public $tid;

    /** @var Torrent */
    protected $torrent;

    protected function isExistTorrent() {
        $tid = $this->getData('tid') ?? $this->getData('id');
        $torrent_exist = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents` WHERE `id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
        if ($torrent_exist == 0) {
            $this->buildCallbackFailMsg('Torrent', 'The torrent id ('. $tid. ') is not exist in our database');
            return;
        }
        $this->torrent = new Torrent($tid);
    }
}
