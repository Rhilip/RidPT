<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 11:30
 */

namespace App\Models\Api\v1\Form;

use App\Models\Form\Traits\isValidTorrentTrait;

use Rid\Validators\Validator;

class TorrentsForm extends Validator
{
    use isValidTorrentTrait;

    public function updateRecord()
    {
        $bookmark_exist = app()->pdo->prepare('SELECT `id` FROM `bookmarks` WHERE `uid` = :uid AND `tid` = :tid ')->bindParams([
            'uid' => app()->auth->getCurUser()->getId(),
            'tid' => $this->getInput('id')
        ])->queryScalar() ?: 0;
        if ($bookmark_exist > 0) {  // Delete the exist record
            app()->pdo->prepare('DELETE FROM `bookmarks` WHERE `id` = :bid')->bindParams([
                'bid' => $bookmark_exist
            ])->execute();
            app()->redis->del('User:' . app()->auth->getCurUser()->getId() . ':bookmark_array');

            return ['msg' => 'Delete Old Bookmark Success', 'result' => 'deleted'];
        } else {  // Add new record
            app()->pdo->prepare('INSERT INTO `bookmarks` (`uid`, `tid`) VALUES (:uid, :tid)')->bindParams([
                'uid' => app()->auth->getCurUser()->getId(),
                'tid' => $this->getInput('id')
            ])->execute();
            app()->redis->del('User:' . app()->auth->getCurUser()->getId() . ':bookmark_array');

            return ['msg' => 'Add New Bookmark Success', 'result' => 'added'];
        }
    }

    public function getFileList()
    {
        $filelist = $this->torrent->getTorrentStructure();
        return ['msg' => 'Get Filelist success', 'result' => $filelist];
    }

    public function getNfoFileContent()
    {
        return ['msg' => 'Get Nfo File Content success', 'result' => $this->torrent->getNfo()];
    }
}
