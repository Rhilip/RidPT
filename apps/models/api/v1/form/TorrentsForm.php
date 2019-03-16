<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 11:30
 */

namespace apps\models\api\v1\form;


use Rid\Validators\Validator;

class TorrentsForm extends Validator
{
    public $tid;

    public static function inputRules()
    {
        return [
            'tid' => 'required | Integer'
        ];
    }

    public static function callbackRules() {
        return ['isExistTorrent'];
    }

    protected function isExistTorrent() {
        $torrent_exist = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents` WHERE `id` = :tid')->bindParams([
            'tid' => $this->tid
        ])->queryScalar();
        if ($torrent_exist == 0) {
            $this->buildCallbackFailMsg('Torrent', 'The torrent id ('. $this->tid. ') is not exist in our database');
        }
    }

    public function updateRecord() {
        $bookmark_exist = app()->pdo->createCommand('SELECT `id` FROM `bookmarks` WHERE `uid` = :uid AND `tid` = :tid ')->bindParams([
            'uid' => app()->user->getId(),
            'tid' => $this->tid
        ])->queryScalar() ?: 0;
        if ($bookmark_exist > 0) {  // Delete the exist record
            app()->pdo->createCommand('DELETE FROM `bookmarks` WHERE `id` = :bid')->bindParams([
                'bid' => $bookmark_exist
            ])->execute();
            app()->redis->del('User:' . app()->user->getId() . ':bookmark_array');

            return ['msg' => 'Delete Old Bookmark Success', 'result' => 'deleted'];
        } else {  // Add new record
            app()->pdo->createCommand('INSERT INTO `bookmarks` (`uid`, `tid`) VALUES (:uid, :tid)')->bindParams([
                'uid' => app()->user->getId(),
                'tid' => $this->tid
            ])->execute();
            app()->redis->del('User:' . app()->user->getId() . ':bookmark_array');

            return ['msg' => 'Add New Bookmark Success', 'result' => 'added'];
        }
    }

    public function getFileList()
    {
        // Check if cache is exist if exist , just quick return
        $filelist = app()->redis->hGet('Torrent:' . $this->tid . ':base_content', 'torrent_structure');
        if ($filelist == false) {
            $filelist = app()->pdo->createCommand('SELECT `torrent_structure` FROM `torrents` WHERE `id`= :tid LIMIT 1')->bindParams([
                'tid' => $this->tid
            ])->queryScalar();
            // However, we don't cache it for cache safety reason.
        }
        return ['msg' => 'Get Filelist success', 'result' => json_decode($filelist, false)];
    }
}
