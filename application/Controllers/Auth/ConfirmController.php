<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Forms\Auth\Confirm\AbstractConfirmForm;
use App\Forms\Auth\Confirm\RecoverForm;
use App\Forms\Auth\Confirm\RegisterForm;
use Rid\Http\AbstractController;

class ConfirmController extends AbstractController
{
    private function confirm(AbstractConfirmForm $form)
    {
        $success = $form->validate();
        if (!$success) {
            return $this->render('action/fail', [
                'title' => 'Confirm Failed',
                'msg' => $form->getError()
            ]);
        } else {
            $form->flush();
            return $this->render('action/success', [
                'notice' => $form->getConfirmMsg(),
                'redirect' => '/auth/login'
            ]);
        }
    }

    public function register()
    {
        $confirm = new RegisterForm();
        $confirm->setInput(container()->get('request')->query->all());
        return $this->confirm($confirm);
    }

    public function recover()
    {
        $confirm = new RecoverForm();
        $confirm->setInput(container()->get('request')->query->all());
        return $this->confirm($confirm);
    }
}
