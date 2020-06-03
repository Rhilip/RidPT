<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 5:20 PM
 */

declare(strict_types=1);

namespace App\Controllers\Blogs;

use App\Forms\Blogs\DeleteForm;
use Rid\Http\AbstractController;

class DeleteController extends AbstractController
{
    public function takeDelete()
    {
        $form = new DeleteForm();
        $form->setInput(container()->get('request')->query->all());
        if ($form->validate()) {
            $form->flush();
            return container()->get('response')->setRedirect('/blogs');
        } else {
            return $this->render('action/fail', ['title' => 'Action Failed', 'msg' => $form->getError()]);
        }
    }
}
