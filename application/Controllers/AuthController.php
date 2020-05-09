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
        if (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->isMethod(Request::METHOD_POST)) {
            $register_form = new Auth\UserRegisterForm();
            $register_form->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->all());
            $success = $register_form->validate();
            if (!$success) {
                return $this->render('action/fail', [
                    'title' => 'Register Failed',
                    'msg' => $register_form->getError()
                ]);
            } else {
                $register_form->flush();  // Save this user in our database and do clean work~

                if ($register_form->getStatus() == UserStatus::CONFIRMED) {
                    return \Rid\Helpers\ContainerHelper::getContainer()->get('response')->setRedirect('/index');
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
        $confirm->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->query->all());
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
        if (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->isMethod(Request::METHOD_POST)) {
            $form = new Auth\UserRecoverForm();
            $form->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->all());
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

        if (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->isMethod(Request::METHOD_POST)) {
            $login = new Auth\UserLoginForm();
            $login->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->all());
            if (false === $success = $login->validate()) {
                $login->loginFail();
                $render_data['error_msg'] =  $login->getError();
            } else {
                $login->flush();

                $return_to = $this->container->get('session')->pop('login_return_to') ?? '/index';
                return \Rid\Helpers\ContainerHelper::getContainer()->get('response')->setRedirect($return_to);
            }
        }

        $render_data['test_attempts'] = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hGet('Site:fail_login_ip_count', \Rid\Helpers\ContainerHelper::getContainer()->get('request')->getClientIp()) ?: 0;
        return $this->render('auth/login', $render_data);
    }

    /** @noinspection PhpUnused */
    public function actionLogout()
    {
        $logout = new Auth\UserLogoutForm();
        $logout->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->query->all());
        if (false === $logout->validate()) {
            return $this->render('action/fail', ['msg' => $logout->getError()]);
        } else {
            $logout->flush();
        }

        return \Rid\Helpers\ContainerHelper::getContainer()->get('response')->setRedirect('/auth/login');
    }
}
