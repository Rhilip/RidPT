<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace App\Controllers;

use App\Models\Form\Torrents;

use Rid\Http\Controller;

class TorrentsController extends Controller
{
    public function actionIndex()
    {
        return $this->actionSearch();
    }

    public function actionSearch()
    {
        $search = new Torrents\SearchForm();
        $search->setInput(app()->request->query->all());
        $success = $search->validate();
        if (!$success) {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }

        return $this->render('torrents/search', ['search' => $search]);
    }

    public function actionTags()
    {
        $pager = new Torrents\TagsForm();
        $pager->setInput(app()->request->query->all());
        $success = $pager->validate();

        if (!$success) {
            return $this->render('action/fail');
        } else {
            $tags = $pager->getPagerData();
            if (count($tags) == 1 && $tags[0]['tag'] == $pager->search) {  // If this search tag is unique and equal to the wanted, just redirect to search page
                return app()->response->setRedirect('/torrents/search?tags=' . $pager->search);
            }
            return $this->render('torrents/tags', ['pager' => $pager]);
        }
    }
}
