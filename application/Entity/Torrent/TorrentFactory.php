<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/27/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\Torrent;

use App\Exceptions\NotExistException;

class TorrentFactory
{
    /**
     * @param $tid
     * @return Torrent
     * @throws NotExistException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getTorrentById($tid): Torrent
    {
        $self = container()->get('dbal')->prepare('SELECT * FROM `torrents` WHERE id=:id LIMIT 1;')->bindParams([
            'id' => $tid
        ])->fetchOne();

        if (false === $self) {
            throw new NotExistException('This Torrent is not exist');  // FIXME
        }

        return new Torrent($self);
    }

    public function getTorrentBySearch(array $search_field, int $offset = 0, int $limit = 50): array
    {
        $fetch = container()->get('dbal')->prepare([
            ['SELECT `id`, `owner_id`, `info_hash`, `status`, `added_at`, `complete`, `incomplete`, `downloaded`, `comments`, `title`, `subtitle`, `category`, `torrent_size`, `team`, `quality_audio`, `quality_codec`, `quality_medium`, `quality_resolution`, `tags`, `uplver`, `hr` FROM `torrents` WHERE 1=1 '],
            ...$search_field,
            ['ORDER BY `added_at` DESC '],
            ['LIMIT :offset, :rows', 'params' => ['offset' => $offset, 'rows' => $limit]]
        ])->fetchAll();

        return array_map(function ($self) {
            return new Torrent($self);
        }, $fetch);
    }
}
