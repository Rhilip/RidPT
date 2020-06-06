<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 8:32 PM
 */

declare(strict_types=1);

namespace App\Controllers\Manage\Categories;

use App\Forms\Manage\Categories\EditForm;
use Rid\Http\AbstractController;

class EditController extends AbstractController
{
    public function takeEdit()
    {
        $form = new EditForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('action/success', ['redirect' => '/manage/categories']);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
