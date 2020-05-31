<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 11:06 PM
 */

namespace App\Models\Form\Traits;

use App\Entity\Torrent\Torrent;
use App\Entity\Torrent\TorrentFactory;

trait isValidTorrentTrait
{
    public $id;  // Torrent Id

    /** @var Torrent */
    protected $torrent;

    public static function inputRules(): array
    {
        return [
            'id' => 'Required | Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return ['isExistTorrent'];
    }

    public function getTorrent(): Torrent
    {
        return $this->torrent;
    }

    /** @noinspection PhpUnused */
    protected function isExistTorrent()
    {
        $tid = $this->getInput('torrent_id') ?? $this->getInput('tid') ?? $this->getInput('id');
        $torrent_exist = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `torrents` WHERE `id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
        if ($torrent_exist == 0) {
            $this->buildCallbackFailMsg('Torrent', 'The torrent id (' . $tid . ') is not exist in our database');
            return;
        }

        $this->torrent = container()->get(TorrentFactory::class)->getTorrentById($tid);
    }

    // TODO check user privilege to see deleted or banned torrent
}
