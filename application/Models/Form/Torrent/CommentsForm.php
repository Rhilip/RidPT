<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 7:55 PM
 */

namespace App\Models\Form\Torrent;

use Rid\Validators\PaginationTrait;

class CommentsForm extends DetailsForm
{
    use PaginationTrait;

    public static $DEFAULT_LIMIT = 20;
    public static $MAX_LIMIT = 50;

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
        return $this->torrent->getComments();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->prepare([
            ['SELECT * FROM `torrent_comments` WHERE torrent_id = :tid', 'params' => ['tid' => $this->id]],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->offset, 'limit' => $this->limit]]
        ])->queryAll();
    }
}
