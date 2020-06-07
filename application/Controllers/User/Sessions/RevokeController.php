<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:06 PM
 */

declare(strict_types=1);

namespace App\Controllers\User\Sessions;

use App\Forms\User\Sessions\RevokeForm;
use Rid\Http\AbstractController;

class RevokeController extends AbstractController
{
    public function takeRevoke()
    {
        $form = new RevokeForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('action/success', ['redirect' => '/user/sessions']);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
