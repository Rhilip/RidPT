<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/21
 * Time: 21:31
 */

namespace App\Controllers;

use App\Models\Form\Torrents;

use Rid\Http\AbstractController;

class TorrentsController extends AbstractController
{
    public function search()
    {
        $search = new Torrents\SearchForm();
        $search->setInput(container()->get('request')->query->all());
        $success = $search->validate();
        if (!$success) {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }

        return $this->render('torrents/search', ['search' => $search]);
    }
}
