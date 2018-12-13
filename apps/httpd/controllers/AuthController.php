<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\httpd\controllers;

use apps\common\facades\SwiftMailer;
use apps\common\libraries\SiteLog;
use apps\common\libraries\SitePM;

use mix\facades\PDO;
use mix\facades\Response;
use mix\facades\Session;
use mix\facades\Request;

use apps\common\facades\Config;

use apps\httpd\models\User;
use apps\httpd\libraries\RandomString;

use mix\http\Controller;
use RobThree\Auth\TwoFactorAuth;


class AuthController extends Controller
{

    public function actionRegister()
    {
        if (Request::isPost()) {
            $username = Request::post("username");
            $password = Request::post("password");
            $password_again = Request::post("password_again");
            $email = filter_var(Request::post("email"), FILTER_VALIDATE_EMAIL);
            $accept_tos = filter_var(Request::post("accept_tos"), FILTER_VALIDATE_BOOLEAN);

            $invite_by = 0;
            $invite_hash = "";

            $status = Config::get("register.user_default_status") ?? USER::STATUS_PENDING;
            $class = Config::get("register.user_default_class") ?? USER::ROLE_USER;
            $uploadpos = Config::get("register.user_default_uploadpos") ?? 1;
            $downloadpos = Config::get("register.user_default_downloadpos") ?? 1;
            $uploaded = Config::get("register.user_default_uploaded") ?? 1;
            $downloaded = Config::get("register.user_default_downloaded") ?? 1;
            $seedtime = Config::get("register.user_default_seedtime") ?? 0;
            $leechtime = Config::get("register.user_default_leechtime") ?? 0;
            $bonus = Config::get("register.user_default_bonus") ?? 0;

            // TODO check if register action is allow
            try {
                $register_type = Request::post("type") ?? "open";
                $this->isRegisterSystemOpen($register_type);

                if (!$register_type || !$username || !$password || !$password_again || !$email || !$accept_tos)
                    throw new \Exception("Miss required item in User Register Form");

                $valid_user_name_res = User::validUsername($username);
                if ($valid_user_name_res)
                    throw new \Exception($valid_user_name_res);

                if ($password !== $password_again) throw new \Exception("Password is not matched");
                if ($username == $password) throw new \Exception("Username equal to password is not allowed");
                if (strlen($password) < 6 || strlen($password) > 40)
                    throw new \Exception("Password is too short or long , Should in `6 - 40`");


                $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example
                $this->emailBlackListCheck($email_suffix);
                $this->emailWhiteListCheck($email_suffix);

                $this->isMaxUserReached();
                $this->isMaxRegisterIpReached();

                $email_check = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `email` = :email")->bindParams([
                    "email" => $email
                ])->queryScalar();
                if ($email_check > 0)
                    throw new \Exception("Email Address '$email' is already Used.");

                if ($register_type == 'invite') {
                    $invite_hash = filter_var(Request::post("invite_hash"), FILTER_SANITIZE_STRING);
                    $hash_length_after_filter = strlen($invite_hash);
                    if ($hash_length_after_filter != 32) {
                        throw new \Exception("This invite hash : `$invite_hash`($hash_length_after_filter) is not valid");
                    } else {
                        $inviteInfo = PDO::createCommand("SELECT * FROM `invite` WHERE `hash`=:invite_hash")->bindParams([
                            "invite_hash" => $invite_hash
                        ])->queryOne();
                        if (!$inviteInfo) {
                            throw new \Exception("This invite hash : `$invite_hash` is not exist");
                        } else {
                            if ($inviteInfo["expire_at"] < time())
                                throw new \Exception("This invite hash is expired at " . $inviteInfo["expire_at"] . ".");
                            $invite_by = $inviteInfo["inviter_id"];
                        }
                    }
                } elseif ($register_type == 'green') {
                    /**
                     * Function that you used to valid that user can register by green ways
                     * By default , It will only throw a NotImplementException.
                     *
                     * For example, Judged by their email suffix , or you can use other method like OATH2
                     *
                     * if (strpos($user_email,"@rhilip.info") !== false)
                     *    // Do something to update $ret_array
                     *
                     * If register pass the Green Check , you can also update some status of this Users.
                     * If he don't pass this check , you should throw Exception with **enough** message.
                     *
                     */
                    throw new \Exception("The Green way to register in this site is not Implemented.");
                }
            } catch (\Exception $e) {
                return $this->render("auth/register_fail.html.twig", [
                    "msg" => $e->getMessage()
                ]);
            }

            /**
             * Set The First User enough privilege ,
             * so that He needn't email (or other way) to confirm his account ,
             * and can access the (super)admin panel to change site config .
             */
            if (USER::fetchUserCount() == 0) {
                $status = USER::STATUS_CONFIRMED;
                $class = USER::ROLE_STAFFLEADER;
            }

            // If pass the validate, then Create this user
            $passkey = RandomString::md5($username . date("Y-m-d H:i:s"), 10);

            PDO::createCommand("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`) 
                                 VALUES (:name, :passhash, :email, :status, :class, :passkey, :invite_by, CURRENT_TIMESTAMP, INET6_ATON(:ip), :uploadpos, :downloadpos, :uploaded, :downloaded, :seedtime, :leechtime, :bonus)")->bindParams(array(
                "name" => $username, "passhash" => password_hash($password, PASSWORD_DEFAULT), "email" => $email,
                "status" => $status, "class" => $class, "passkey" => $passkey,
                "invite_by" => $invite_by, "ip" => Request::getClientIp(),
                "uploadpos" => $uploadpos, "downloadpos" => $downloadpos,
                "uploaded" => $uploaded, "downloaded" => $downloaded,
                "seedtime" => $seedtime, "leechtime" => $leechtime,
                "bonus" => $bonus
            ))->execute();
            $userId = PDO::getLastInsertId();

            if ($register_type == 'invite') {
                PDO::createCommand("DELETE from `invite` WHERE `hash` = :invite_hash")->bindParams([
                    "invite_hash" => $invite_hash,
                ])->execute();

                // FIXME Send PM to inviter
                SitePM::send(0, $invite_by, "New Invitee Signup Successful", "New Invitee Signup Successful");
            }

            SiteLog::write("User $username($userId) is created now" . (
                $register_type == "invite" ? ", Invite by " : ""
                ), SiteLog::LEVEL_MOD);

            if ($status == User::STATUS_CONFIRMED) {
                return Response::redirect("/index");
            } else {
                switch (Config::get("register.user_confirm_way")) {
                    case "auto":
                        PDO::createCommand("UPDATE `users` SET `status` = :s WHERE `id` = :id")->bindParams([
                            "s" => User::STATUS_CONFIRMED, "id" => $userId,
                        ])->execute();
                        return Response::redirect("/index");
                    case "mod":
                        return $this->render('auth/register_pending.html.twig', [
                            "confirm_way" => "mod",
                        ]);
                    case "email":
                    default:
                        {
                            // FIXME send mail or other confirm way to active this new user (change it's status to `confirmed`)
                            SwiftMailer::send([$email], "Please confirm your accent", "Click this link to confirm.");
                            return $this->render('auth/register_pending.html.twig', [
                                "confirm_way" => "email",
                                "email" => $email
                            ]);
                        }
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

    /**
     * @param $type
     * @throws \Exception
     */
    private function isRegisterSystemOpen($type)
    {
        if (Config::get("base.enable_register_system") != true)
            throw new \Exception("The register isn't open in this site");
        if (!in_array($type, ['open', 'invite', 'green']))
            throw new \Exception("The Register Type {$type} is not allowed");
        if (Config::get("register.by_" . $type) != true)
            throw new \Exception("The register by {$type} ways isn't open in this site");
    }

    /**
     * @throws \Exception
     */
    private function isMaxUserReached()
    {
        if (Config::get("register.max_user_check") &&
            USER::fetchUserCount() >= Config::get("base.max_user")) {
            throw new \Exception("Max user limit Reached");
        }
    }

    private function isMaxLoginIpReached()
    {

    }

    /**
     * @throws \Exception
     */
    private function isMaxRegisterIpReached()
    {
        if (Config::get("register.max_ip_check")) {
            $client_ip = Request::getClientIp();

            $max_user_per_ip = Config::get("register.per_ip_user") ?: 5;
            $user_ip_count = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)")->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip)
                throw new \Exception("The register member count in this ip $client_ip is reached");
        }
    }

    /**
     * @param $email_suffix
     * @throws \Exception
     */
    private function emailBlackListCheck($email_suffix)
    {
        if (Config::get("register.enabled_email_black_list") &&
            Config::get("register.email_black_list")) {
            $email_black_list = explode(",", Config::get("register.email_black_list"));
            if (in_array($email_suffix, $email_black_list))
                throw new \Exception("The email suffix `$email_suffix` is not allowed.");
        }
    }

    /**
     * @param $email_suffix
     * @throws \Exception
     */
    private function emailWhiteListCheck($email_suffix)
    {
        if (Config::get("register.enabled_email_white_list") &&
            Config::get("register.email_white_list")) {
            $email_white_list = explode(",", Config::get("register.email_white_list"));
            if (!in_array($email_suffix, $email_white_list))
                throw new \Exception("The email suffix `$email_suffix` is not allowed.");
        }
    }
}