<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/27/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\Torrent;

class TorrentStatus
{
    const DELETED = 'deleted';
    const BANNED = 'banned';
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';

    const TORRENT_STATUSES = [
        self::BANNED,
        self::CONFIRMED,
        self::PENDING,
        self::BANNED,
    ];
}
