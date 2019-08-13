<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 7:55 PM
 */

namespace apps\models\form\Torrent;


use Rid\Validators\PagerTrait;

class CommentsForm extends DetailsForm
{
    use PagerTrait;

    public static $DEFAULT_LIMIT = 20;
    public static $MAX_LIMIT = 50;

    public static function inputRules()
    {
        return [
            'id' => 'required | Integer',
            'page' => 'Integer', 'limit' => 'Integer'
        ];
    }

    public static function callbackRules()
    {
        return ['isExistTorrent', 'checkPager'];
    }

    protected function getRemoteTotal(): int
    {
        return $this->torrent->getComments();
    }

    protected function getRemoteData(): array
    {
        return app()->pdo->createCommand([
            ['SELECT * FROM `torrent_comments` WHERE torrent_id = :tid', 'params' => ['tid' => $this->getInputId()]],
            ['LIMIT :offset, :limit', 'params' => ['offset' => $this->offset, 'limit' => $this->limit]]
        ])->queryAll();
    }
}
