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
        $tid = $this->getData('tid');
        $torrent_exist = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents` WHERE `id` = :tid')->bindParams([
            'tid' => $tid
        ])->queryScalar();
        if ($torrent_exist == 0) {
            $this->buildCallbackFailMsg('Torrent', 'The torrent id ('. $tid. ') is not exist in our database');
        }
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
        // Check if cache is exist if exist , just quick return
        $filelist = app()->redis->hGet('Torrent:' . $this->tid . ':base_content', 'torrent_structure');
        if ($filelist === false) {
            $filelist = app()->pdo->createCommand('SELECT `torrent_structure` FROM `torrents` WHERE `id`= :tid LIMIT 1')->bindParams([
                'tid' => $this->tid
            ])->queryScalar();
            // However, we don't cache it for cache safety reason.
        }
        return ['msg' => 'Get Filelist success', 'result' => json_decode($filelist, false)];
    }

    public function getNfoFileContent()
    {
        $nfo_file = app()->redis->hGet('Torrent:' . $this->tid . ':base_content', 'nfo');
        if ($nfo_file === false) {
            $nfo_file = app()->pdo->createCommand('SELECT `nfo` FROM `torrents` WHERE `id` = :tid LIMIT 1')->bindParams([
                'tid' => $this->tid
            ])->queryScalar();
        }

        // Convert nfo
        $nfo_file = self::code($nfo_file);

        return ['msg' => 'Get Nfo File Content success','result' => $nfo_file];
    }


    // FIXME Code for Viewing NFO file

    // code: Takes a string and does a IBM-437-to-HTML-Unicode-Entities-conversion.
    // swedishmagic specifies special behavior for Swedish characters.
    // Some Swedish Latin-1 letters collide with popular DOS glyphs. If these
    // characters are between ASCII-characters (a-zA-Z and more) they are
    // treated like the Swedish letters, otherwise like the DOS glyphs.
    protected static function code($ibm_437, $swedishmagic = false)
    {
        $cf = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 8962, 199, 252, 233, 226, 228, 224, 229, 231, 234, 235, 232, 239, 238, 236, 196, 197, 201, 230, 198, 244, 246, 242, 251, 249, 255, 214, 220, 162, 163, 165, 8359, 402, 225, 237, 243, 250, 241, 209, 170, 186, 191, 8976, 172, 189, 188, 161, 171, 187, 9617, 9618, 9619, 9474, 9508, 9569, 9570, 9558, 9557, 9571, 9553, 9559, 9565, 9564, 9563, 9488, 9492, 9524, 9516, 9500, 9472, 9532, 9566, 9567, 9562, 9556, 9577, 9574, 9568, 9552, 9580, 9575, 9576, 9572, 9573, 9561, 9560, 9554, 9555, 9579, 9578, 9496, 9484, 9608, 9604, 9612, 9616, 9600, 945, 223, 915, 960, 931, 963, 181, 964, 934, 920, 937, 948, 8734, 966, 949, 8745, 8801, 177, 8805, 8804, 8992, 8993, 247, 8776, 176, 8729, 183, 8730, 8319, 178, 9632, 160);
        $s = "";
        for ($c = 0; $c < strlen($ibm_437); $c++) {  // cyctle through the whole file doing a byte at a time.
            $byte = $ibm_437[$c];
            $ob = ord($byte);
            if ($ob >= 127) {  // is it in the normal ascii range
                $s .= '&#' . $cf[$ob] . ';';
            } else {
                $s .= $byte;
            }
        }
        if ($swedishmagic) {
            $s = str_replace("\345", "\206", $s);
            $s = str_replace("\344", "\204", $s);
            $s = str_replace("\366", "\224", $s);

            // couldn't get ^ and $ to work, even through I read the man-pages,
            // i'm probably too tired and too unfamiliar with posix regexps right now.
            $s = preg_replace("/([ -~])\305([ -~])/", "\\1\217\\2", $s);
            $s = preg_replace("/([ -~])\304([ -~])/", "\\1\216\\2", $s);
            $s = preg_replace("/([ -~])\326([ -~])/", "\\1\231\\2", $s);
            $s = str_replace("\311", "\220", $s); //
            $s = str_replace("\351", "\202", $s); //
        }
        return $s;
    }
}
