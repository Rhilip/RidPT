<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 18:57
 */

namespace App\Models\Form\Auth;

use App\Libraries\Constant;
use App\Entity\User\UserRole;
use App\Entity\User\UserStatus;
use App\Entity\Site\LogLevel;

use Rid\Helpers\StringHelper;
use Rid\Validators\Validator;
use Rid\Validators\CaptchaTrait;

class UserRegisterForm extends Validator
{
    use CaptchaTrait;

    public $type = 'open';
    public $username;
    public $password;
    public $email;

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

    public static function defaultData(): array
    {
        return [
            'type' => 'open',
            'verify_tos' => 0,
            'verify_age' => 0,
            'invite_by' => 0,
            'invite_hash' => '',
        ];
    }

    public static function inputRules(): array
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
                ['NotMatch', ['item' => 'username']]
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

    public static function callbackRules(): array
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
        $this->status = config('register.user_default_status') ?? UserStatus::PENDING;
        $this->class = config('register.user_default_class') ?? UserRole::USER;
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
            $this->buildCallbackFailMsg('RegisterSystemOpen', 'The register isn\'t open in this site.');
            return;
        }

        if (config('register.by_' . $this->getInput('type')) != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen', "The register by {$this->getInput('type')} ways isn't open in this site.");
            return;
        }
    }

    protected function isMaxUserReached()
    {
        if (config('register.check_max_user') &&
            app()->site::fetchUserCount() >= config('base.max_user')) {
            $this->buildCallbackFailMsg('MaxUserReached', 'Max user limit Reached');
        }
    }

    protected function isMaxRegisterIpReached()
    {
        if (config('register.check_max_ip')) {
            $client_ip = app()->request->getClientIp();

            $max_user_per_ip = config('register.per_ip_user') ?: 5;
            $user_ip_count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)')->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip) {
                $this->buildCallbackFailMsg('MaxRegisterIpReached', "The register member count in this ip `$client_ip` is reached");
            }
        }
    }

    protected function isValidUsername()
    {
        $username = $this->getInput('username');

        // The following characters are allowed in user names
        if (strspn(strtolower($username), 'abcdefghijklmnopqrstuvwxyz0123456789_') != strlen($username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'Invalid characters in user names.');
            return;
        }

        // Check if this username is in blacklist or not
        if (app()->redis->sIsMember(Constant::siteBannedUsernameSet, $username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'This username is in our blacklist.');
            return;
        }

        // Check this username is exist in Table `users` or not
        $count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `users` WHERE `username` = :username')->bindParams([
            'username' => $username
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('ValidUsername', "The user name `$username` is already used.");
            return;
        }
    }

    protected function isValidEmail()
    {
        $email = $this->getInput('email');
        $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example

        if (config('register.check_email_blacklist') &&
            in_array($email_suffix, config('register.email_black_list'))) {
            $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
            return;
        }

        if (config('register.check_email_whitelist') &&
            !in_array($email_suffix, config('register.email_white_list'))) {
            $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
            return;
        }

        // Check $email is in blacklist or not
        if (app()->redis->sIsMember(Constant::siteBannedEmailSet, $email)) {
            $this->buildCallbackFailMsg('ValidEmail', 'This email is in our blacklist.');
            return;
        }

        $email_check = app()->pdo->prepare('SELECT COUNT(`id`) FROM `users` WHERE `email` = :email')->bindParams([
            "email" => $email
        ])->queryScalar();
        if ($email_check > 0) {
            $this->buildCallbackFailMsg('ValidEmail', "Email Address '$email' is already used.");
            return;
        }
    }

    protected function checkRegisterType()
    {
        $type = $this->getInput('type');
        if ($type == 'invite') {
            $invite_hash = $this->getInput('invite_hash');
            if (strlen($invite_hash) != 32) {
                $this->buildCallbackFailMsg('Invite', "This invite hash : `$invite_hash` is not valid");
                return;
            } else {
                $inviteInfo = app()->pdo->prepare('SELECT * FROM `invite` WHERE `hash`=:invite_hash AND `used` = 0 AND `expire_at` > NOW() LIMIT 1;')->bindParams([
                    'invite_hash' => $invite_hash
                ])->queryOne();
                if (false === $inviteInfo) {
                    $this->buildCallbackFailMsg('Invite', "This invite hash : `$invite_hash` is not exist or may already used or expired.");
                    return;
                }

                // TODO config key of enable username check
                if ($this->getInput('username') != $inviteInfo['username']) {
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
        if (app()->site::fetchUserCount() == 0) {
            $this->status = UserStatus::CONFIRMED;
            $this->class = UserRole::STAFFLEADER;
            $this->confirm_way = 'auto';
        }

        // User status should be confirmed if site confirm_way is auto
        if ($this->confirm_way == 'auto' and $this->status != UserStatus::CONFIRMED) {
            $this->status = UserStatus::CONFIRMED;
        }

        // Insert into `users` table and get insert id
        app()->pdo->prepare("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`,`invites`)
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

        // TODO Newcomer exams

        $log_text = "User $this->username($this->id) is created now.";

        // Send Invite Success PM to invitee
        if ($this->type == 'invite') {
            app()->pdo->prepare("UPDATE `invite` SET `used` = 1 WHERE `hash` = :invite_hash")->bindParams([
                "invite_hash" => $this->invite_hash,
            ])->execute();

            $invitee = app()->site->getUser($this->invite_by);
            $log_text .= '(Invite by ' . $invitee->getUsername() . '(' . $invitee->getId() . ')).';

            app()->site->sendPM(0, $this->invite_by, 'New Invitee Signup Successful', "New Invitee Signup Successful");
        }

        // Send Confirm Email
        if ($this->confirm_way == 'email') {
            $confirm_key = StringHelper::getRandomString(32);
            app()->pdo->prepare('INSERT INTO `user_confirm` (`uid`,`secret`,`create_at`,`action`) VALUES (:uid,:secret,CURRENT_TIMESTAMP,:action)')->bindParams([
                'uid' => $this->id, 'secret' => $confirm_key, 'action' => $this->_action
            ])->execute();
            $confirm_url = app()->request->getSchemeAndHttpHost() . '/auth/confirm?' . http_build_query([
                    'secret' => $confirm_key,
                    'action' => $this->_action
                ]);

            app()->site->sendEmail(
                [$this->email],
                'Please confirm your accent',
                'email/user_register',
                [
                    'username' => $this->username,
                    'confirm_url' => $confirm_url,
                ]
            );
        }

        // Add Site log for user signup
        app()->site->writeLog($log_text, LogLevel::LOG_LEVEL_MOD);
    }
}
