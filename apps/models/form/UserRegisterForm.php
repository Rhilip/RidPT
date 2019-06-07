<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 18:57
 */

namespace apps\models\form;

use apps\libraries\Site;

use apps\models\User;

use Rid\Helpers\StringHelper;
use Rid\Http\View;
use Rid\Validators\Validator;
use Rid\Validators\CaptchaTrait;

class UserRegisterForm extends Validator
{
    use CaptchaTrait;

    public $id;

    public $username;
    public $password;
    public $password_again;
    public $email;
    public $accept_tos = 0;

    public $type = 'open';

    public $invite_by = 0;
    public $invite_hash = "";
    public $confirm_way;

    public $status;

    private $passkey;
    private $class;
    private $uploadpos;
    private $downloadpos;
    private $uploaded;
    private $downloaded;
    private $seedtime;
    private $leechtime;
    private $bonus;
    private $invites;

    protected $_action = 'register';

    public function setData($config)
    {
        parent::setData($config);
        $this->buildDefaultValue();
    }

    public function buildDefaultValue()
    {
        $this->status = app()->config->get('register.user_default_status') ?? User::STATUS_PENDING;
        $this->class = app()->config->get('register.user_default_class') ?? User::ROLE_USER;
        $this->uploadpos = app()->config->get('register.user_default_uploadpos') ?? 1;
        $this->downloadpos = app()->config->get('register.user_default_downloadpos') ?? 1;
        $this->uploaded = app()->config->get('register.user_default_uploaded') ?? 1;
        $this->downloaded = app()->config->get('register.user_default_downloaded') ?? 1;
        $this->seedtime = app()->config->get('register.user_default_seedtime') ?? 0;
        $this->leechtime = app()->config->get('register.user_default_leechtime') ?? 0;
        $this->bonus = app()->config->get('register.user_default_bonus') ?? 0;
        $this->confirm_way = app()->config->get('register.user_confirm_way') ?? 'auto';
        $this->invites = app()->config->get('register.user_default_invites') ?? 0;
    }

    public static function inputRules()
    {
        return [
            'type' => [
                ['required'],
                ['InList', ['list' => ['open', 'invite', 'green']], 'The Register Type is not allowed']
            ],
            'username' => [
                ['required'],
                ['MaxLength', ['max' => 12], 'User name is too log, Max length {max}']
            ],
            'password' => [
                ['required'],
                ['length', '6,40'],
                // TODO The password cannot match your username.
            ],
            'password_again' => [
                ['required'],
                ['Match', ['item' => 'password']]
            ],
            'email' => 'required | email',
            'verify_tos' => 'required | Equal(value=yes)',
            'verify_age' => 'required | Equal(value=yes)',
        ];
    }

    public static function callbackRules()
    {
        return [
            'validateCaptcha',
            'isRegisterSystemOpen', 'isMaxUserReached', 'isMaxRegisterIpReached',
            'isValidUsername', 'isValidEmail',
            'checkRegisterType'
        ];
    }

    protected function isRegisterSystemOpen()
    {
        if (app()->config->get('base.enable_register_system') != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen','The register isn\'t open in this site.');
            return;
        }

        if (app()->config->get('register.by_' . $this->type) != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen',"The register by {$this->type} ways isn't open in this site.");
            return;
        }
    }

    protected function isMaxUserReached()
    {
        if (app()->config->get('register.check_max_user') &&
            Site::fetchUserCount() >= app()->config->get('base.max_user'))
            $this->buildCallbackFailMsg('MaxUserReached','Max user limit Reached');
    }

    public function isMaxRegisterIpReached()
    {
        if (app()->config->get('register.check_max_ip')) {
            $client_ip = app()->request->getClientIp();

            $max_user_per_ip = app()->config->get('register.per_ip_user') ?: 5;
            $user_ip_count = app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)")->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip) {
                $this->buildCallbackFailMsg('MaxRegisterIpReached',"The register member count in this ip `$client_ip` is reached");
            }
        }
    }

    public function isValidUsername()
    {
        $username = $this->username;
        // The following characters are allowed in user names
        if (strspn(strtolower($username), 'abcdefghijklmnopqrstuvwxyz0123456789_') != strlen($username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'Invalid characters in user names.');
            return;
        }

        $count = app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users` WHERE `username` = :username")->bindParams([
            "username" => $username
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('ValidUsername', "The user name `$username` is already used.");
            return;
        }
    }

    public function isValidEmail()
    {
        $email = $this->email;
        $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example
        if (app()->config->get('register.check_email_blacklist') &&
            app()->config->get('register.email_black_list')) {
            $email_black_list = explode(",", app()->config->get('register.email_black_list'));
            if (in_array($email_suffix, $email_black_list)) {
                $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
                return;
            }
        }
        if (app()->config->get('register.check_email_whitelist') &&
            app()->config->get('register.email_white_list')) {
            $email_white_list = explode(",", app()->config->get('register.email_white_list'));
            if (!in_array($email_suffix, $email_white_list)) {
                $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
                return;
            }
        }

        $email_check = app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users` WHERE `email` = :email")->bindParams([
            "email" => $email
        ])->queryScalar();
        if ($email_check > 0) {
            $this->buildCallbackFailMsg('ValidEmail', "Email Address '$email' is already used.");
            return;
        }
    }

    protected function checkRegisterType()
    {
        if ($this->type == 'invite') {
            if (strlen($this->invite_hash) != 32) {
                $this->buildCallbackFailMsg('Invite', "This invite hash : `$this->invite_hash` is not valid");
                return;
            } else {
                $inviteInfo = app()->pdo->createCommand("SELECT * FROM `invite` WHERE `hash`=:invite_hash")->bindParams([
                    "invite_hash" => $this->invite_hash
                ])->queryOne();
                if (!$inviteInfo) {
                    $this->buildCallbackFailMsg('Invite', "This invite hash : `$this->invite_hash` is not exist");
                    return;
                } else {
                    if ($this->username != $inviteInfo['username']) {
                        $this->buildCallbackFailMsg('Invite', "This invite username is not match.");
                        return;
                    }

                    if ($inviteInfo["expire_at"] < time()) {
                        $this->buildCallbackFailMsg('Invite', "This invite hash is expired at " . $inviteInfo["expire_at"] . ".");
                        return;
                    }
                }
            }
        } elseif ($this->type == 'green') {
            /**
             * Function that you used to valid that user can register by green ways
             * By default , It will only throw a NotImplementViolation
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
            $this->buildCallbackFailMsg('Green', "The Green way to register in this site is not Implemented.");
            return;
        }
    }

    public function flush()
    {
        $this->passkey = StringHelper::md5($this->username . date("Y-m-d H:i:s"), 10);

        /**
         * Set The First User enough privilege ,
         * so that He needn't email (or other way) to confirm his account ,
         * and can access the (super)admin panel to change site config .
         */
        if (Site::fetchUserCount() == 0) {
            $this->status = User::STATUS_CONFIRMED;
            $this->class = User::ROLE_STAFFLEADER;
            $this->confirm_way = "auto";
        }

        if ($this->confirm_way == 'auto' and $this->status != User::STATUS_CONFIRMED) {
            $this->status = User::STATUS_CONFIRMED;
        }

        app()->pdo->createCommand("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`,`invites`) 
                                 VALUES (:name, :passhash, :email, :status, :class, :passkey, :invite_by, CURRENT_TIMESTAMP, INET6_ATON(:ip), :uploadpos, :downloadpos, :uploaded, :downloaded, :seedtime, :leechtime, :bonus, :invites)")->bindParams(array(
            'name' => $this->username, 'passhash' => password_hash($this->password, PASSWORD_DEFAULT), 'email' => $this->email,
            'status' => $this->status, 'class' => $this->class, 'passkey' => $this->passkey,
            'invite_by' => $this->invite_by, 'ip' => app()->request->getClientIp(),
            'uploadpos' => $this->uploadpos, 'downloadpos' => $this->downloadpos,
            'uploaded' => $this->uploaded, 'downloaded' => $this->downloaded,
            'seedtime' => $this->seedtime, 'leechtime' => $this->leechtime,
            'bonus' => $this->bonus , 'invites' => $this->invites
        ))->execute();
        $this->id = app()->pdo->getLastInsertId();

        $log_text = "User $this->username($this->id) is created now.";

        if ($this->type == 'invite') {
            app()->pdo->createCommand("UPDATE `invite` SET `used` = 1 WHERE `hash` = :invite_hash")->bindParams([
                "invite_hash" => $this->invite_hash,
            ])->execute();

            $invitee = new User($this->invite_by);
            $log_text .= '(Invite by ' . $invitee->getUsername() . '(' . $invitee->getId() . ')).';

            Site::sendPM(0, $this->invite_by, 'New Invitee Signup Successful', "New Invitee Signup Successful");
        }

        if ($this->confirm_way == 'email') {
            $confirm_key = StringHelper::getRandomString(32);
            app()->pdo->createCommand('INSERT INTO `user_confirm` (`uid`,`secret`,`create_at`,`action`) VALUES (:uid,:secret,CURRENT_TIMESTAMP,:action)')->bindParams([
                'uid' => $this->id, 'secret' => $confirm_key, 'action' => $this->_action
            ])->execute();
            $confirm_url = app()->request->root() . '/auth/confirm?' . http_build_query([
                    'secret' => $confirm_key,
                    'action' => $this->_action
                ]);

            $mail_body = (new View(false))->render('email/user_register', [
                'username' => $this->username,
                'confirm_url' => $confirm_url,
            ]);
            $mail_sender = \apps\libraries\Mailer::newInstanceByConfig('libraries.[mailer]');
            $mail_sender->send([$this->email], 'Please confirm your accent', $mail_body);
        }

        Site::writeLog($log_text, Site::LOG_LEVEL_MOD);
    }
}
