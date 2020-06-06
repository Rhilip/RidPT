<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 8:31 PM
 */

declare(strict_types=1);

namespace App\Controllers\Manage\Categories;

use App\Forms\Manage\Categories\IndexForm;
use Rid\Http\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        $form = new IndexForm();
        $form->setInput(container()->get('request')->query->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('manage/categories', ['form' => $form]);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
