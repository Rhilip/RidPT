<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 4:43 PM
 */

declare(strict_types=1);

namespace App\Controllers\Blogs;

use App\Forms\Blogs\EditForm;
use App\Forms\Blogs\ExistForm;
use Rid\Http\AbstractController;

class EditController extends AbstractController
{
    public function index() {
        $form = new ExistForm();
        $form->setInput(container()->get('request')->query->all());
        if ($form->validate()) {
            return $this->render('blogs/edit', ['news' => $form->getBlog()]);
        } else {
            return $this->render('action/fail', ['title' => 'Action Failed', 'msg' => $form->getError()]);
        }
    }

    public function takeEdit() {
        $form = new EditForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();  // Save the news
            return container()->get('response')->setRedirect('/blogs');
        } else {
            return $this->render('action/fail', ['title' => 'Edit Failed', 'msg' => $form->getError()]);
        }
    }
}
