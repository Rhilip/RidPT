<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 11:47 AM
 */

declare(strict_types=1);

namespace App\Controllers\Invite;

use App\Forms\Invite\IndexForm;
use App\Forms\Invite\InviteForm;
use Rid\Http\AbstractController;

class IndexController extends AbstractController
{
    public function index()
    {
        $form = new IndexForm();
        $form->setInput(container()->get('request')->query->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('invite/index', ['form' => $form]);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }

    /** @noinspection PhpUnused */
    public function takeInvite()
    {
        $form = new InviteForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->index();
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
