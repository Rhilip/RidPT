<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 5:18 PM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\TagsForm;
use Rid\Utils\Arr;
use Rid\Http\AbstractController;

class TagsController extends AbstractController
{
    public function index()
    {
        $tags = new TagsForm();
        $tags->setInput(container()->get('request')->query->all());
        if ($tags->validate()) {
            $tags->flush();
            // If this search tag is unique and equal to the wanted, just redirect to search page
            if (count($tags->getPaginationData()) == 1 && $tags->getPaginationData()[0]['tag'] == $tags->getInput('search')) {
                return container()->get('response')->setRedirect('/torrents/search?' . Arr::query([
                        'tags' => $tags->getInput('search')
                    ]));
            } else {
                return $this->render('torrents/tags', ['tags' => $tags]);
            }
        } else {
            return $this->render('action/fail', ['msg' => $tags->getError()]);
        }
    }
}
