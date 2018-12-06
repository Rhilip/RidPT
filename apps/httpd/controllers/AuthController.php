<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\httpd\controllers;

use apps\common\facades\Config;
use apps\httpd\libraries\RandomStringFactory;
use mix\facades\PDO;
use mix\facades\Token;
use mix\facades\Request;

use apps\httpd\models\UserForm;

use RobThree\Auth\TwoFactorAuth;


class AuthController
{
    /**
     * @return array
     */
    public function actionRegister()
    {
        // TODO check if register action is allow
        $model = new UserForm();
        $model->attributes = Request::post();
        $model->setScenario('create');
        if (!$model->validate()) {
            // FIXME Unified interface specification
            return ['code' => 1, 'message' => 'FAILED', 'data' => $model->getErrors()];
        }

        // If pass the validate, then Create this user
        $passkey = RandomStringFactory::md5($model->username . date("Y-m-d H:i:s"), 10);

        // Set default value
        $status = Config::get("register.user_default_status") ?: "pending";
        $class = Config::get("register.user_default_class") ?: 1;
        $uploadpos = Config::get("register.user_default_uploadpos") ?: 1;
        $downloadpos = Config::get("register.user_default_downloadpos") ?: 1;
        $uploaded = Config::get("register.user_default_uploaded") ?: 1;
        $downloaded = Config::get("register.user_default_downloaded") ?: 1;
        $seedtime = Config::get("register.user_default_seedtime") ?: 0;
        $leechtime = Config::get("register.user_default_leechtime") ?: 0;
        $bonus = Config::get("register.user_default_bonus") ?: 0;

        /**
         * Get The First User enough privilege ,
         * so that He needn't email (or other way) to confirm his account ,
         * and can access the (super)admin panel to change site config .
         */
        if ($this->fetchUserCount() == 0) {
            $status = "confirmed";
            $class = 100;
        }

        PDO::createCommand("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`) 
                                 VALUES (:name, :passhash, :email, :status, :class, :passkey, :invite_by, CURRENT_TIMESTAMP, INET6_ATON(:ip), :uploadpos, :downloadpos, :uploaded, :downloaded, :seedtime, :leechtime, :bonus)")->bindParams(array(
            "name" => $model->username, "passhash" => password_hash($model->password, PASSWORD_BCRYPT), "email" => $model->email,
            "status" => $status, "class" => $class, "passkey" => $passkey,
            "invite_by" => 0, "ip" => Request::getClientIp(),
            "uploadpos" => $uploadpos, "downloadpos" => $downloadpos,
            "uploaded" => $uploaded, "downloaded" => $downloaded,
            "seedtime" => $seedtime, "leechtime" => $leechtime,
            "bonus" => $bonus
        ))->execute();

        // TODO Deleted Invite Code and then Send PM to inviter

        // TODO send mail or other confirm way to active this new user (change it's status to `confirmed`)

        // FIXME Unified interface specification
        return ['code' => 1, 'message' => 'Success'];
    }

    public function actionConfirm()
    {

    }

    public function actionRecover()
    {

    }

    public function actionLogin()
    {
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
                    throw new \Exception("Invalid username/password");
            }

            // User 's status is banned or pending~
            if (in_array($self["status"], ["banned", "pending"])) {
                throw new \Exception("User account is not confirmed.");
            }
        } catch (\Exception $e) {
            // TODO return login fail data
            return "faild";
        }

        Token::createTokenId();
        Token::set('userinfo', [
            'uid' => $self["id"],
            'username' => $self["username"],
            'status' => $self["status"]
        ]);
        Token::setUniqueIndex($self["id"]);

        // FIXME Unified interface specification
        return [
            'errcode' => 0,
            'access_token' => Token::getTokenId(),
            'expires_in' => app()->token->expiresIn,
        ];

    }

    private function fetchUserCount()
    {
        return PDO::createCommand("SELECT COUNT(`id`) FROM `users`")->queryScalar();
    }

    /**
     * @throws \Exception
     */
    private function isRegisterSystemOpen()
    {
        if (Config::get("base.enable_register_system") != true)
            throw new \Exception("The register isn't open in this site");
        //if ($config->get("base.enable_register_system") != true)
    }

    /**
     * @throws \Exception
     */
    private function isMaxUserReach()
    {
        if (Config::get("register.max_user_check")) {
            $userCount = $this->fetchUserCount();
            $maxUserCount = Config::get("base.max_user");
            if ($userCount >= $maxUserCount) throw new \Exception("Max user limit Reached");
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
     * @throws \Exception
     */
    private function canRegisterByInvite()
    {
        if (Config::get("register.by_invite") != true)
            throw new \Exception("The register by invite ways isn't open in this site");
    }

    /**
     * @throws \Exception
     */
    private function canRegisterByOpen()
    {
        if (Config::get("register.by_open") != true)
            throw new \Exception("The register by open ways isn't open in this site.");
    }

    /**
     * @throws \Exception
     */
    private function canRegisterByGreen()
    {
        if (Config::get("register.by_green") != true)
            throw new \Exception("The register by green ways isn't open in this site.");
    }
}