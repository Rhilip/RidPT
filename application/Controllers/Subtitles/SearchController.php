<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:05 AM
 */

declare(strict_types=1);

namespace App\Controllers\Subtitles;

use App\Forms\Subtitles\SearchForm;
use Rid\Http\AbstractController;

class SearchController extends AbstractController
{
    public function index($upload = null)
    {
        $search = new SearchForm();
        $search->setInput(container()->get('request')->query->all());
        if ($search->validate()) {
            $search->flush();
            return $this->render('subtitles/search', ['search' => $search, 'upload_mode' => $upload]);
        } else {
            return $this->render('action/fail', ['msg' => $search->getError()]);
        }
    }
}
