<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:06 PM
 */

declare(strict_types=1);

namespace App\Controllers\User;

use App\Forms\User\DetailsFrom;
use Rid\Http\AbstractController;

class DetailsController extends AbstractController
{
    public function index()
    {
        $form = new DetailsFrom();
        $form->setInput(container()->get('request')->query->all());
        if ($form->validate()) {
            return $this->render('user/details', ['details' => $form]);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
