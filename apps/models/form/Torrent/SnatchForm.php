<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 8:40 PM
 */

namespace apps\models\form\Torrent;

use apps\models\form\Traits\isValidTorrentTrait;
use Rid\Validators\Pager;

class SnatchForm extends Pager
{
    use isValidTorrentTrait;

    public static function inputRules()
    {
        return array_merge(parent::inputRules(), [
            'id' => 'required | Integer'
        ]);
    }

    public static function callbackRules()
    {
        return ['checkPager', 'isExistTorrent'];
    }

    protected function getRemoteTotal(): int
    {
        $tid = $this->getData('id');
        return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `snatched` WHERE `torrent_id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->createCommand([
            ['SELECT * FROM `snatched` WHERE `torrent_id` = :tid ORDER BY finish_at,create_at DESC ', 'params' => ['tid' => $this->id]],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->offset, 'limit' => $this->limit]]
        ])->queryAll();
    }
}
