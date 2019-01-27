<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\controllers;

use apps\models\User;
use apps\models\form\UserRegisterForm;

use Mix\Http\Controller;
use RobThree\Auth\TwoFactorAuth;


class AuthController extends Controller
{

    public function actionRegister()
    {
        if (app()->request->isPost()) {
            $user = new UserRegisterForm();
            $user->importAttributes(app()->request->post());
            $error = $user->validate();
            if (count($error) > 0) {
                return $this->render("auth/register_fail.html.twig", [
                    "msg" => $error->get(0)
                ]);
            } else {
                $user->flush();  // Save this user in our database and do clean work~

                if ($user->status == User::STATUS_CONFIRMED) {
                    return app()->response->redirect("/index");
                } else {
                    return $this->render('auth/register_pending.html.twig', [
                        "confirm_way" => $user->confirm_way,
                        "email" => $user->email
                    ]);
                }
            }
        } else {
            return $this->render("auth/register.html.twig");
        }
    }

    public function actionConfirm()
    {
        // TODO User Confirm Action
    }

    public function actionRecover()
    {
        // TODO User Recover Action
    }

    public function actionLogin()
    {
        if (app()->request->isPost()) {
            $username = app()->request->post("username");
            $self = app()->pdo->createCommand("SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1")->bindParams([
                "uname" => $username, "email" => $username,
            ])->queryOne();

            try {
                // User is not exist
                if (!$self) throw new \Exception("Invalid username/password");

                // User's password is not correct
                if (!password_verify(app()->request->post("password"), $self["password"]))
                    throw new \Exception("Invalid username/password");

                // User enable 2FA but it's code is wrong
                if ($self["opt"]) {
                    $tfa = new TwoFactorAuth(app()->config->get("base.site_name"));
                    if ($tfa->verifyCode($self["opt"], app()->request->post("opt")) == false)
                        throw new \Exception("2FA Validation failed");
                }

                // User 's status is banned or pending~
                if (in_array($self["status"], ["banned", "pending"])) {
                    throw new \Exception("User account is not confirmed.");
                }
            } catch (\Exception $e) {
                return $this->render("auth/login.html.twig", ["username" => $username, "error_msg" => $e->getMessage()]);
            }

            app()->user->createUserSessionId($self["id"]);

            app()->pdo->createCommand("UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id")->bindParams([
                "ip" => app()->request->getClientIp(), "id" => $self["id"]
            ])->execute();

            $return_to = app()->session->pop('login_return_to') ?? '/index';
            return app()->response->redirect($return_to);
        } else {
            return $this->render("auth/login.html.twig");
        }
    }

    public function actionLogout()
    {
        // TODO add CSRF protect
        app()->user->deleteUserThisSession();
        return app()->response->redirect('/auth/login');
    }

    private function isMaxLoginIpReached()
    {

    }
}
