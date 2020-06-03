<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 12:31 PM
 */

declare(strict_types=1);

namespace App\Controllers\Blogs;

use App\Forms\Blogs\CreateForm;
use Rid\Http\AbstractController;

class CreateController extends AbstractController
{
    public function index()
    {
        return $this->render('blogs/edit');
    }

    /** @noinspection PhpUnused */
    public function takeCreate()
    {
        $form = new CreateForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();  // Save the news
            return container()->get('response')->setRedirect('/blogs');
        } else {
            return $this->render('action/fail', ['title' => 'new blog failed', 'msg' => $form->getError()]);
        }
    }
}
