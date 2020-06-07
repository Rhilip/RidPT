<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:06 PM
 */

declare(strict_types=1);

namespace App\Controllers\User\Sessions;

use App\Forms\User\Sessions\ListForm;
use Rid\Http\AbstractController;

class ListController extends AbstractController
{
    public function index()
    {
        $form = new ListForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('user/sessions/index', ['form' => $form]);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
