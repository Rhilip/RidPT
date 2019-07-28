<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 18:57
 */

namespace apps\models\form\Auth;

use apps\models\User;
use apps\libraries\Site;

use Rid\Helpers\StringHelper;
use Rid\Validators\Validator;
use Rid\Validators\CaptchaTrait;

class UserRegisterForm extends Validator
{
    use CaptchaTrait;

    public $type = 'open';
    public $username;
    public $password;
    public $password_again;
    public $email;
    public $verify_tos = 0;
    public $verify_age = 0;

    public $invite_by = 0;
    public $invite_hash = "";

    private $id;
    private $status;
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
    private $confirm_way;

    protected $_action = 'register';

    public static function defaultData()
    {
        return [
            'type' => 'open',
            'verify_tos' => 0,
            'verify_age' => 0,
            'invite_by' => 0,
            'invite_hash' => '',
        ];
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
            'password' => [  // TODO The password cannot match your username. ( make change to validator library
                ['required'],
                ['length', '6,40'],
            ],
            'password_again' => [
                ['required'],
                ['Match', ['item' => 'password']]
            ],
            'email' => 'required | email',
            'verify_tos' => 'required | Equal(value=1)',
            'verify_age' => 'required | Equal(value=1)',
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

    public function buildDefaultPropAfterValid()
    {
        $this->status = config('register.user_default_status') ?? User::STATUS_PENDING;
        $this->class = config('register.user_default_class') ?? User::ROLE_USER;
        $this->uploadpos = config('register.user_default_uploadpos') ?? 1;
        $this->downloadpos = config('register.user_default_downloadpos') ?? 1;
        $this->uploaded = config('register.user_default_uploaded') ?? 1;
        $this->downloaded = config('register.user_default_downloaded') ?? 1;
        $this->seedtime = config('register.user_default_seedtime') ?? 0;
        $this->leechtime = config('register.user_default_leechtime') ?? 0;
        $this->bonus = config('register.user_default_bonus') ?? 0;
        $this->confirm_way = config('register.user_confirm_way') ?? 'auto';
        $this->invites = config('register.user_default_invites') ?? 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getConfirmWay(): string
    {
        return $this->confirm_way;
    }

    protected function isRegisterSystemOpen()
    {
        if (config('base.enable_register_system') != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen','The register isn\'t open in this site.');
            return;
        }

        if (config('register.by_' . $this->getData('type')) != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen',"The register by {$this->getData('type')} ways isn't open in this site.");
            return;
        }
    }

    protected function isMaxUserReached()
    {
        if (config('register.check_max_user') &&
            Site::fetchUserCount() >= config('base.max_user'))
            $this->buildCallbackFailMsg('MaxUserReached','Max user limit Reached');
    }

    protected function isMaxRegisterIpReached()
    {
        if (config('register.check_max_ip')) {
            $client_ip = app()->request->getClientIp();

            $max_user_per_ip = config('register.per_ip_user') ?: 5;
            $user_ip_count = app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)")->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip) {
                $this->buildCallbackFailMsg('MaxRegisterIpReached',"The register member count in this ip `$client_ip` is reached");
            }
        }
    }

    protected function isValidUsername()
    {
        $username = $this->getData('username');

        // The following characters are allowed in user names
        if (strspn(strtolower($username), 'abcdefghijklmnopqrstuvwxyz0123456789_') != strlen($username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'Invalid characters in user names.');
            return;
        }

        // Check if this username is not in blacklist
        if (!app()->redis->exists('site:username_ban_list')) {
            $ban_username_list = app()->pdo->createCommand('SELECT `username` from `ban_usernames`')->queryColumn();
            app()->redis->hMset('site:username_ban_list', $ban_username_list);
            app()->redis->expire('site:username_ban_list', 86400);
        }
        if (app()->redis->hExists('site:username_ban_list', $username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'This username is in our blacklist.');
            return;
        }

        // Check this username is exist in Table `users` or not
        $count = app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users` WHERE `username` = :username")->bindParams([
            "username" => $username
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('ValidUsername', "The user name `$username` is already used.");
            return;
        }
    }

    protected function isValidEmail()
    {
        $email = $this->getData('email');
        $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example
        if (config('register.check_email_blacklist') &&
            config('register.email_black_list')) {
            $email_black_list = explode(",", config('register.email_black_list'));
            if (in_array($email_suffix, $email_black_list)) {
                $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
                return;
            }
        }

        if (config('register.check_email_whitelist') &&
            config('register.email_white_list')) {
            $email_white_list = explode(",", config('register.email_white_list'));
            if (!in_array($email_suffix, $email_white_list)) {
                $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
                return;
            }
        }

        // Check $email is not in blacklist
        if (!app()->redis->exists('site:emails_ban_list')) {
            $ban_email_list = app()->pdo->createCommand('SELECT `email` from `ban_emails`')->queryColumn();
            app()->redis->hMset('site:emails_ban_list', $ban_email_list);
            app()->redis->expire('site:emails_ban_list', 86400);
        }
        if (app()->redis->hExists('site:emails_ban_list', $email)) {
            $this->buildCallbackFailMsg('ValidEmail', 'This email is in our blacklist.');
            return;
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
        $type = $this->getData('type');
        if ($type == 'invite') {
            $invite_hash = $this->getData('invite_hash');
            if (strlen($invite_hash) != 32) {
                $this->buildCallbackFailMsg('Invite', "This invite hash : `$invite_hash` is not valid");
                return;
            } else {
                $inviteInfo = app()->pdo->createCommand('SELECT * FROM `invite` WHERE `hash`=:invite_hash AND `used` = 0 AND `expire_at` > NOW() LIMIT 1;')->bindParams([
                    'invite_hash' => $invite_hash
                ])->queryOne();
                if (false === $inviteInfo) {
                    $this->buildCallbackFailMsg('Invite', "This invite hash : `$invite_hash` is not exist or may already used or expired.");
                    return;
                }

                // TODO config key of enable username check
                if ($this->getData('username') != $inviteInfo['username']) {
                    $this->buildCallbackFailMsg('Invite', "This invite username is not match.");
                    return;
                }
            }
        } elseif ($type == 'green') {
            /**
             * Function that you used to valid that user can register by green ways
             * By default , It will only throw a NotImplementViolation
             *
             * For example, Judged by their email suffix , or you can use other method like OATH2
             *
             * if (strpos($user_email,"@rhilip.info") !== false)
             *    // Do something to update $ret_array
             *
             * If register pass the Green Check , you can also update some status of this Site.
             * If he don't pass this check , you should `buildCallbackFailMsg` with **enough** message.
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

            Site::sendEmail([$this->email], 'Please confirm your accent',
                'email/user_register', [
                    'username' => $this->username,
                    'confirm_url' => $confirm_url,
                ]);
        }

        Site::writeLog($log_text, Site::LOG_LEVEL_MOD);
    }
}
