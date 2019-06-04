<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\controllers;

use apps\models\User;
use apps\models\form\UserLoginForm;
use apps\models\form\UserConfirmForm;
use apps\models\form\UserRecoverForm;
use apps\models\form\UserRegisterForm;

use Rid\Http\Controller;


class AuthController extends Controller
{

    public function actionRegister()
    {
        if (app()->request->isPost()) {
            $user = new UserRegisterForm();
            $user->setData(app()->request->post());
            $success = $user->validate();
            if (!$success) {
                return $this->render('auth/error', [
                    'title' => 'Register Failed',
                    'msg' => $user->getError()
                ]);
            } else {
                $user->flush();  // Save this user in our database and do clean work~

                if ($user->status == User::STATUS_CONFIRMED) {
                    return app()->response->redirect('/index');
                } else {
                    return $this->render('auth/register_pending', [
                        'confirm_way' => $user->confirm_way,
                        'email' => $user->email
                    ]);
                }
            }
        } else {
            return $this->render('auth/register');
        }
    }

    public function actionConfirm()
    {
        $confirm = new UserConfirmForm();
        $confirm->setData(app()->request->get());
        $success = $confirm->validate();
        if (!$success) {
            return $this->render('auth/error', [
                'title' => 'Confirm Failed',
                'msg' => $confirm->getError()
            ]);
        } else {
            $confirm->flush();
            return $this->render('auth/confirm_success', ['action' => $confirm->action]);
        }
    }

    public function actionRecover()
    {
        if (app()->request->isPost()) {
            $form = new UserRecoverForm();
            $form->setData(app()->request->post());
            $success = $form->validate();
            if (!$success) {
                return $this->render('auth/error', [
                    'title' => 'Action Failed',
                    'msg' => $form->getError()
                ]);
            } else {
                $flush = $form->flush();
                if ($flush === true) {
                    return $this->render('auth/recover_next_step');
                } else {
                    return $this->render('auth/error', [
                        'title' => 'Confirm Failed',
                        'msg' => $flush
                    ]);
                }
            }
        } else {
            return $this->render('auth/recover');
        }
    }

    public function actionLogin()
    {
        $test_attempts = app()->redis->hGet('SITE:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
        $left_attempts = app()->config->get('security.max_login_attempts') - $test_attempts;

        if (app()->request->isPost()) {
            $login = new UserLoginForm();
            $login->setData(app()->request->post());
            $success = $login->validate();

            if (!$success) {
                $login->LoginFail();
                return $this->render('auth/login', [
                    "username" => $login->username,
                    "error_msg" => $login->getError(),
                    'left_attempts' => $left_attempts
                ]);
            } else {
                $success = $login->createUserSession();
                if ($success === true) {
                    $login->updateUserLoginInfo();

                    $return_to = app()->session->pop('login_return_to') ?? '/index';
                    if (!app()->request->isSecure() && $login->ssl === 'yes') {  // Upgrade the scheme with full url
                        $return_to = 'https://' . app()->request->header('host') . $return_to;
                    }

                    return app()->response->redirect($return_to);
                } else {
                    return $this->render('auth/error', ['title' => 'Login Failed', 'msg' => $success]);
                }
            }
        } else {
            return $this->render('auth/login', ['left_attempts' => $left_attempts]);
        }
    }

    public function actionLogout()
    {
        // TODO add CSRF protect
        app()->user->deleteUserThisSession();
        return app()->response->redirect('/auth/login');
    }
}
