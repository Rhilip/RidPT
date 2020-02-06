<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/6/2019
 * Time: 8:40 PM
 */

namespace App\Models\Form\Torrent;

use Rid\Validators\PaginationTrait;

class SnatchForm extends DetailsForm
{
    use PaginationTrait;

    public static $MAX_LIMIT = 100;

    public static function inputRules(): array
    {
        return [
            'id' => 'required | Integer',
            'page' => 'Integer', 'limit' => 'Integer'
        ];
    }

    public static function callbackRules(): array
    {
        return ['isExistTorrent', 'checkPager'];
    }

    protected function getRemoteTotal(): int
    {
        $tid = $this->getInput('id');
        return app()->pdo->prepare('SELECT COUNT(`id`) FROM `snatched` WHERE `torrent_id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->prepare([
            ['SELECT * FROM `snatched` WHERE `torrent_id` = :tid ORDER BY finish_at,create_at DESC ', 'params' => ['tid' => $this->id]],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->offset, 'limit' => $this->limit]]
        ])->queryAll();
    }
}
