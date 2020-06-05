<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 10:14 PM
 */

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Entity\Torrent\Torrent;
use App\Entity\Torrent\TorrentFactory;
use App\Exceptions\NotExistException;

trait isValidTorrentTrait
{
    protected ?Torrent $torrent;

    protected function isExistTorrent()
    {
        try {
            $this->torrent = container()->get(TorrentFactory::class)->getTorrentById($this->getTorrentId());
        } catch (NotExistException $e) {
            $this->buildCallbackFailMsg('Torrent', 'This Torrent is not exist');
        }
    }

    abstract public function getTorrentId() :int;

    /**
     * @return Torrent|null
     */
    public function getTorrent(): ?Torrent
    {
        return $this->torrent;
    }
}
