<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\httpd\controllers;

use apps\common\traits\TorrentTraits;

use apps\httpd\models\TorrentUploadForm;
use Mix\Facades\Request;
use Mix\Http\Controller;

use SandFoxMe\Bencode\Bencode;
use SandFoxMe\Bencode\Exceptions\ParseErrorException;

class TorrentsController extends Controller
{
    use TorrentTraits;

    public function actionIndex()
    {

    }

    public function actionUpload()
    {
        // TODO Check user upload pos
        if (Request::isPost()) {
            $model = new TorrentUploadForm();
            $model->attributes = Request::post();
            $model->setScenario('upload');
            if (!$model->validate()) {
                return $this->render("torrents/upload_fail.html.twig", ["msg" => $model->getError()]);
            }

            // TODO valid filename

            // TODO Check user can Upload anonymous
            $model->file->size;
            $dict = Bencode::load($model->file->tmpName);
            $info = $this->checkTorrentDict($dict, 'info');
            $dname = $this->checkTorrentDict($info, 'name', 'string');
            $plen = $this->checkTorrentDict($info, 'piece length', 'integer');
            $pieces = $this->checkTorrentDict($info, 'pieces', 'string');

            if (strlen($pieces) % 20 != 0)
                throw new ParseErrorException("std_invalid_pieces");

            $filelist = [];
            if (isset($info['length'])) {
                $totallen = $info['length'];
                $filelist[] = [$dname, $totallen];
                $type = "single";
            } else {
                $flist = $this->checkTorrentDict($info, "files", "array");
                if (!isset($flist)) throw new ParseErrorException('std_missing_length_and_files');
                if (!count($flist)) throw new ParseErrorException('no files');
                $totallen = 0;
                var_dump($flist);
                foreach ($flist as $fn) {
                    $ll = $this->checkTorrentDict($fn, "length", "integer");
                    $ff = $this->checkTorrentDict($fn, "path", "list");
                    $totallen += $ll;
                    $ffa = [];
                    foreach ($ff as $ffe) {
                        if (is_string($ffe)) throw new ParseErrorException('std_filename_errors');
                        $ffa[] = $ffe;
                    }
                    if (!count($ffa)) throw new ParseErrorException('std_filename_errors');
                    $ffe = implode("/", $ffa);
                    $filelist[] = array($ffe, $ll);
                }
                $type = "multi";
            }

            $dict['announce'] = "http://ridpt.rhilip.info";  // TODO change announce url to local
            $dict['info']['private'] = 1;  // add private tracker flag
            // The following line requires uploader to re-download torrents after uploading
            // even the torrent is set as private and with uploader's passkey in it.
            $dict['info']['source'] = "Powered by RidPT";  // TODO
            unset($dict['announce-list']); // remove multi-tracker capability
            unset($dict['nodes']); // remove cached peers (Bitcomet & Azareus)

            // Get info_hash on new torrent content dict
            $info_hash = pack("H*", sha1(Bencode::encode($dict['info'])));

            return "Pass";
        } else {
            // TODO Check user can upload
            return $this->render("torrents/upload.html.twig");
        }

    }

    public function actionSearch()
    {

    }
}
