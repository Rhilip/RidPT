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
    public function search()
    {
        $search = new Torrents\SearchForm();
        $search->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->query->all());
        $success = $search->validate();
        if (!$success) {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }

        return $this->render('torrents/search', ['search' => $search]);
    }

    public function tags()
    {
        $pager = new Torrents\TagsForm();
        $pager->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->query->all());
        $success = $pager->validate();

        if (!$success) {
            return $this->render('action/fail');
        } else {
            $tags = $pager->getPagerData();
            if (count($tags) == 1 && $tags[0]['tag'] == $pager->search) {  // If this search tag is unique and equal to the wanted, just redirect to search page
                return \Rid\Helpers\ContainerHelper::getContainer()->get('response')->setRedirect('/torrents/search?tags=' . $pager->search);
            }
            return $this->render('torrents/tags', ['pager' => $pager]);
        }
    }
}
