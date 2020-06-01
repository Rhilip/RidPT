<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Auth;


use App\Entity\User\UserStatus;
use App\Forms\Auth\RegisterForm;
use Rid\Http\AbstractController;

class RegisterController extends AbstractController
{
    public function index() {
        return $this->render('auth/register');
    }

    /** @noinspection PhpUnused */
    public function takeRegister() {
        $register_form = new RegisterForm();
        $register_form->setInput(container()->get('request')->request->all());
        if (!$register_form->validate()) {
            return $this->render('action/fail', [
                'title' => 'Register Failed',
                'msg' => $register_form->getError()
            ]);
        } else {
            $register_form->flush();  // Save this user in our database and do clean work~

            if ($register_form->getStatus() == UserStatus::CONFIRMED) {
                return container()->get('response')->setRedirect('/index');
            } else {
                return $this->render('auth/register_pending', [
                    'confirm_way' => $register_form->getConfirmWay(),
                    'email' => $register_form->getInput('email')
                ]);
            }
        }
    }
}
