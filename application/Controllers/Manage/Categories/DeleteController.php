<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:17 PM
 */

declare(strict_types=1);

namespace App\Controllers\Manage\Categories;

use App\Forms\Manage\Categories\DeleteForm;
use Rid\Http\AbstractController;

class DeleteController extends AbstractController
{
    public function takeDelete()
    {
        $form = new DeleteForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('action/success', ['redirect' => '/manage/categories']);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
