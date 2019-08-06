<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace apps\controllers;

use apps\models\form\Torrents;

use Rid\Http\Controller;

class TorrentsController extends Controller
{

    public function actionIndex()
    {
        return $this->actionSearch();
    }

    public function actionSearch()
    {
        // TODO add URI level Cache
        $pager = new Torrents\SearchForm();
        $pager->setData(app()->request->get());
        $success = $pager->validate();
        if (!$success) {
            return $this->render('action/action_fail');
        }

        return $this->render('torrents/list', ['pager' => $pager]);
    }

    public function actionUpload()
    {
        // TODO Check user upload pos
        if (app()->request->isPost()) {
            $uploadForm = new Torrents\UploadForm();
            $uploadForm->setData(app()->request->post());
            $uploadForm->setFileData(app()->request->files());
            $success = $uploadForm->validate();
            if (!$success) {
                return $this->render('action/action_fail', ['title' => 'Upload Failed', 'msg' => $uploadForm->getError()]);
            } else {
                try {
                    $uploadForm->flush();
                } catch (\Exception $e) {
                    return $this->render('action/action_fail', ['title' => 'Upload Failed', 'msg' => $e->getMessage()]);
                }

                return app()->response->redirect('/torrent/details?id=' . $uploadForm->id);
            }
        } else {
            return $this->render('torrents/upload');
        }

    }

    public function actionTags()
    {
        $pager = new Torrents\TagsForm();
        $pager->setData(app()->request->get());
        $success = $pager->validate();

        if (!$success) {
            return $this->render('action/action_fail');
        } else {
            $tags = $pager->getPagerData();
            if (count($tags) == 1 && $tags[0]['tag'] == $pager->search) {  // If this search tag is unique and equal to the wanted, just redirect to search page
                return app()->response->redirect('/torrents/search?tags=' . $pager->search);
            }
            return $this->render('torrents/tags', ['pager' => $pager]);
        }

    }
}
