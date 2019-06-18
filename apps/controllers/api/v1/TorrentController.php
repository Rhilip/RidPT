<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 10:39
 */

namespace apps\controllers\api\v1;

use apps\models\api\v1\form\TorrentsForm;
use Rid\Http\ApiController;

class TorrentController extends ApiController
{
    public function actionBookmark() {
        if ($this->checkMethod('POST')) {
            $bookmark = new TorrentsForm();
            $bookmark->setData(app()->request->post());
            $success = $bookmark->validate();
            if (!$success) {
                return [
                    'success' => false,
                    'errors' => $bookmark->getErrors()
                ];
            } else {
                $ret = $bookmark->updateRecord();
                return array_merge(
                    ['success' => true],
                    $ret
                );
            }
        } else {
            return $this->buildMethodFailMsg('POST');
        }
    }

    public function actionFileList() {
        if ($this->checkMethod('GET')) {
            $filelist = new TorrentsForm();
            $filelist->setData(app()->request->get());
            $success = $filelist->validate();
            if (!$success) {
                return [
                    'success' => false,
                    'errors' => $filelist->getErrors()
                ];
            } else {
                $ret = $filelist->getFileList();
                return array_merge(
                    ['success' => true],
                    $ret
                );
            }

        } else {
            return $this->buildMethodFailMsg('GET');
        }
    }
}
