<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 11:30
 */

namespace apps\models\api\v1\form;

use apps\models\form\Traits\isValidTorrentTrait;

use Rid\Validators\Validator;

class TorrentsForm extends Validator
{
    use isValidTorrentTrait;

    public static function inputRules()
    {
        return [
            'tid' => 'required | Integer'
        ];
    }

    public static function callbackRules() {
        return ['isExistTorrent'];
    }

    public function updateRecord() {
        $bookmark_exist = app()->pdo->createCommand('SELECT `id` FROM `bookmarks` WHERE `uid` = :uid AND `tid` = :tid ')->bindParams([
            'uid' => app()->site->getCurUser()->getId(),
            'tid' => $this->tid
        ])->queryScalar() ?: 0;
        if ($bookmark_exist > 0) {  // Delete the exist record
            app()->pdo->createCommand('DELETE FROM `bookmarks` WHERE `id` = :bid')->bindParams([
                'bid' => $bookmark_exist
            ])->execute();
            app()->redis->del('User:' . app()->site->getCurUser()->getId() . ':bookmark_array');

            return ['msg' => 'Delete Old Bookmark Success', 'result' => 'deleted'];
        } else {  // Add new record
            app()->pdo->createCommand('INSERT INTO `bookmarks` (`uid`, `tid`) VALUES (:uid, :tid)')->bindParams([
                'uid' => app()->site->getCurUser()->getId(),
                'tid' => $this->tid
            ])->execute();
            app()->redis->del('User:' . app()->site->getCurUser()->getId() . ':bookmark_array');

            return ['msg' => 'Add New Bookmark Success', 'result' => 'added'];
        }
    }

    public function getFileList()
    {
        $filelist = $this->torrent->getTorrentStructure();
        return ['msg' => 'Get Filelist success', 'result' => json_decode($filelist, false)];
    }

    public function getNfoFileContent()
    {
        return ['msg' => 'Get Nfo File Content success','result' => $this->torrent->getNfo()];
    }
}
