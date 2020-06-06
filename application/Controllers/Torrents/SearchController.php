<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 8:04 PM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\SearchForm;
use Rid\Http\AbstractController;

class SearchController extends AbstractController
{
    public function index()
    {
        $search = new SearchForm();
        $search->setInput(container()->get('request')->query->all());
        if ($search->validate()) {
            $search->flush();
            return $this->render('torrents/search', ['search' => $search]);
        } else {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }
    }
}
