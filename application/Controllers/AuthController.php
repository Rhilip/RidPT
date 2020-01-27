<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace App\Controllers;

use App\Models\Form\Auth;
use App\Entity\User\UserStatus;

use Rid\Http\Controller;
use Symfony\Component\HttpFoundation\Request;

class AuthController extends Controller
{

    /** @noinspection PhpUnused */
    public function actionRegister()
    {
        if (app()->request->isMethod(Request::METHOD_POST)) {
            $register_form = new Auth\UserRegisterForm();
            $register_form->setInput(app()->request->request->all());
            $success = $register_form->validate();
            if (!$success) {
                return $this->render('action/fail', [
                    'title' => 'Register Failed',
                    'msg' => $register_form->getError()
                ]);
            } else {
                $register_form->flush();  // Save this user in our database and do clean work~

                if ($register_form->getStatus() == UserStatus::CONFIRMED) {
                    return app()->response->setRedirect('/index');
                } else {
                    return $this->render('auth/register_pending', [
                        'confirm_way' => $register_form->getConfirmWay(),
                        'email' => $register_form->email
                    ]);
                }
            }
        } else {
            return $this->render('auth/register');
        }
    }

    /** @noinspection PhpUnused */
    public function actionConfirm()
    {
        $confirm = new Auth\UserConfirmForm();
        $confirm->setInput(app()->request->query->all());
        $success = $confirm->validate();
        if (!$success) {
            return $this->render('action/fail', [
                'title' => 'Confirm Failed',
                'msg' => $confirm->getError()
            ]);
        } else {
            $confirm->flush();
            return $this->render('action/success', [
                'notice' => $confirm->getConfirmMsg(),
                'redirect' => '/auth/login'
            ]);
        }
    }

    /** @noinspection PhpUnused */
    public function actionRecover()
    {
        if (app()->request->isMethod(Request::METHOD_POST)) {
            $form = new Auth\UserRecoverForm();
            $form->setInput(app()->request->request->all());
            $success = $form->validate();
            if (!$success) {
                return $this->render('action/fail', [
                    'title' => 'Action Failed',
                    'msg' => $form->getError()
                ]);
            } else {
                $flush = $form->flush();
                if ($flush === true) {
                    return $this->render('auth/recover_next_step');
                } else {
                    return $this->render('action/fail', [
                        'title' => 'Confirm Failed',
                        'msg' => $flush
                    ]);
                }
            }
        } else {
            return $this->render('auth/recover');
        }
    }

    /** @noinspection PhpUnused */
    public function actionLogin()
    {
        $render_data = [];

        if (app()->request->isMethod(Request::METHOD_POST)) {
            $login = new Auth\UserLoginForm();
            if (false === $success = $login->validate()) {
                $login->loginFail();
                $render_data['error_msg'] =  $login->getError();
            } else {
                $login->flush();

                $return_to = app()->session->pop('login_return_to') ?? '/index';
                return app()->response->setRedirect($return_to);
            }
        }

        $render_data['test_attempts'] = app()->redis->hGet('Site:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
        return $this->render('auth/login', $render_data);
    }

    /** @noinspection PhpUnused */
    public function actionLogout()
    {
        $logout = new Auth\UserLogoutForm();
        if (false === $logout->validate()) {
            return $this->render('action/fail', ['msg' => $logout->getError()]);
        } else {
            $logout->flush();
        }

        return app()->response->setRedirect('/auth/login');
    }
}
