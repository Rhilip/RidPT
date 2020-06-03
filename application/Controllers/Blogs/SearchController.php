<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 10:57 AM
 */

declare(strict_types=1);

namespace App\Controllers\Blogs;

use App\Forms\Blogs\SearchForm;
use Rid\Http\AbstractController;

class SearchController extends AbstractController
{
    public function index()
    {
        $search_form = new SearchForm();
        $search_form->setInput(container()->get('request')->query->all());
        if ($search_form->validate()) {
            $search_form->flush();
            return $this->render('blogs/index', ['pager' => $search_form]);
        } else {
            return $this->render('action/fail', ['title' => 'Attack', 'msg' => $search_form->getError()]);
        }
    }
}
