<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 11:30
 */

namespace App\Models\Api\v1\Form;

use App\Forms\Traits\isValidTorrentTrait;
use Rid\Validators\Validator;

class TorrentsForm extends Validator
{
    use isValidTorrentTrait;

    public function updateRecord()
    {
        $bookmark_exist = container()->get('pdo')->prepare('SELECT `id` FROM `bookmarks` WHERE `uid` = :uid AND `tid` = :tid ')->bindParams([
            'uid' => container()->get('auth')->getCurUser()->getId(),
            'tid' => $this->getInput('id')
        ])->queryScalar() ?: 0;
        if ($bookmark_exist > 0) {  // Delete the exist record
            container()->get('pdo')->prepare('DELETE FROM `bookmarks` WHERE `id` = :bid')->bindParams([
                'bid' => $bookmark_exist
            ])->execute();
            container()->get('auth')->getCurUser()->updateBookmarkList();

            return ['msg' => 'Delete Old Bookmark Success', 'result' => 'deleted'];
        } else {  // Add new record
            container()->get('pdo')->prepare('INSERT INTO `bookmarks` (`uid`, `tid`) VALUES (:uid, :tid)')->bindParams([
                'uid' => container()->get('auth')->getCurUser()->getId(),
                'tid' => $this->getInput('id')
            ])->execute();
            container()->get('auth')->getCurUser()->updateBookmarkList();

            return ['msg' => 'Add New Bookmark Success', 'result' => 'added'];
        }
    }

    public function getFileList()
    {
        $filelist = $this->torrent->getTorrentStructure();
        return ['msg' => 'Get Filelist success', 'result' => $filelist];
    }

    public function getTorrentId()
    {
        return $this->getInput('id');
    }
}
