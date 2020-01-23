<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/16
 * Time: 10:39
 */

namespace App\Controllers\Api\v1;

use App\Models\Api\v1\Form\TorrentsForm;

class TorrentController extends ApiController
{
    public function actionBookmark()
    {
        if ($this->checkMethod('POST')) {
            $bookmark = new TorrentsForm();
            $bookmark->setInput(app()->request->request->all());
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

    public function actionFileList()
    {
        if ($this->checkMethod('GET')) {
            $filelist = new TorrentsForm();
            $filelist->setInput(app()->request->query->all());
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

    public function actionNfoFileContent()
    {
        if ($this->checkMethod('GET')) {
            $filelist = new TorrentsForm();
            $filelist->setInput(app()->request->query->all());
            $success = $filelist->validate();
            if (!$success) {
                return [
                    'success' => false,
                    'errors' => $filelist->getErrors()
                ];
            } else {
                $ret = $filelist->getNfoFileContent();
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
