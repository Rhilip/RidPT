<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\httpd\controllers;

use Mix\Facades\PDO;
use Mix\Facades\Response;
use Mix\Facades\Session;
use Mix\Facades\Request;

use Mix\Facades\Config;

use apps\httpd\models\User;
use apps\httpd\models\form\UserRegisterForm;

use Mix\Http\Controller;
use RobThree\Auth\TwoFactorAuth;


class AuthController extends Controller
{

    public function actionRegister()
    {
        if (Request::isPost()) {
            $user = new UserRegisterForm();
            $user->importAttributes(Request::post());
            $error = $user->validate();
            if (count($error) > 0) {
                return $this->render("auth/register_fail.html.twig", [
                    "msg" => $error->get(0)
                ]);
            } else {
                $user->flush();  // Save this user in our database and do clean work~

                if ($user->status == User::STATUS_CONFIRMED) {
                    return Response::redirect("/index");
                } else {
                    return $this->render('auth/register_pending.html.twig', [
                        "confirm_way" => Config::get("register.user_confirm_way"),
                        "email" => $user->email
                    ]);
                }
            }
        } else {
            return $this->render("auth/register.html.twig", [
                "register_type" => Request::get("type") ?? "open",
                "invite_hash" => Request::get("invite_hash")
            ]);
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
        if (Request::isPost()) {
            $username = Request::post("username");
            $self = PDO::createCommand("SELECT `id`,`username`,`password`,`status`,`opt` from users WHERE `username` = :uname OR `email` = :email LIMIT 1")->bindParams([
                "uname" => $username, "email" => $username,
            ])->queryOne();

            try {
                // User is not exist
                if (!$self) throw new \Exception("Invalid username/password");

                // User's password is not correct
                if (!password_verify(Request::post("password"), $self["password"]))
                    throw new \Exception("Invalid username/password");

                // User enable 2FA but it's code is wrong
                if ($self["opt"]) {
                    $tfa = new TwoFactorAuth(Config::get("base.site_name"));
                    if ($tfa->verifyCode($self["opt"], Request::post("opt")) == false)
                        throw new \Exception("2FA Validation failed");
                }

                // User 's status is banned or pending~
                if (in_array($self["status"], ["banned", "pending"])) {
                    throw new \Exception("User account is not confirmed.");
                }
            } catch (\Exception $e) {
                return $this->render("auth/login.html.twig", ["username" => $username, "error_msg" => $e->getMessage()]);
            }

            Session::createSessionId();
            Session::set('userInfo', [
                'uid' => $self["id"],
                'username' => $self["username"],
                'status' => $self["status"]
            ]);

            PDO::createCommand("UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id")->bindParams([
                "ip" => Request::getClientIp(), "id" => $self["id"]
            ])->execute();

            return Response::redirect('/index');
        } else {
            return $this->render("auth/login.html.twig");
        }
    }

    public function actionLogout()
    {
        // TODO add CSRF protect
        Session::delete('userInfo');
        return Response::redirect('/auth/login');
    }

    private function isMaxLoginIpReached()
    {

    }
}
