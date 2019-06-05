<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace apps\models\form;

use apps\libraries\Constant;
use apps\components\User\UserInterface;

use Rid\Helpers\StringHelper;

use Rid\Validators\CaptchaTrait;
use Rid\Validators\Validator;

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;


class UserLoginForm extends Validator
{
    use CaptchaTrait;

    public $username;
    public $password;
    public $opt;

    public $logout;
    public $securelogin;
    public $ssl;

    private $self;

    // Key Information of User Session
    private $sessionLength = 64;
    private $sessionSaveKey = 'SESSION:user_set';

    // Cookie
    private $cookieExpires = 0x7fffffff;
    private $cookiePath = '/';
    private $cookieDomain = '';
    private $cookieSecure = false;  // Notice : Only change this value when you first run !!!!
    private $cookieHttpOnly = true;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->sessionSaveKey = app()->user->sessionSaveKey;
    }

    public static function inputRules()
    {
        return [
            'username' => 'required',
            'password' => [
                ['required'],
                ['length', ['min' => 6, 'max' => 40]]
            ],
            'opt' => 'length(6)',
            'securelogin' => 'Equal(value=yes)',
            'logout' => 'Equal(value=yes)',
            'ssl' => 'Equal(value=yes)',
        ];
    }

    public static function callbackRules()
    {
        return ['validateCaptcha', 'loadUserFromPdo', 'isMaxLoginIpReached'];
    }

    protected function loadUserFromPdo()
    {
        $this->self = app()->pdo->createCommand("SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1")->bindParams([
            "uname" => $this->username, "email" => $this->username,
        ])->queryOne();

        if (!$this->self) {  // User is not exist
            /** Notice: We shouldn't tell `This User is not exist in this site.` for user information security. */
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User's password is not correct
        if (!password_verify($this->password, $this->self["password"])) {
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User enable 2FA but it's code is wrong
        if ($this->self["opt"]) {
            try {
                $tfa = new TwoFactorAuth(app()->config->get("base.site_name"));
                if ($tfa->verifyCode($this->self["opt"], $this->opt) == false) {
                    $this->buildCallbackFailMsg('2FA', '2FA Validation failed. Check your device time.');
                    return;
                }
            } catch (TwoFactorAuthException $e) {
                $this->buildCallbackFailMsg('2FA', '2FA Validation failed. Tell our support team.');
                return;
            }
        }

        // User 's status is banned or pending~
        if (in_array($this->self["status"], [UserInterface::STATUS_BANNED, UserInterface::STATUS_PENDING])) {
            $this->buildCallbackFailMsg('Account', 'User account is not confirmed.');
            return;
        }
    }

    protected function isMaxLoginIpReached()
    {
        $test_count = app()->redis->hGet('SITE:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
        if ($test_count > app()->config->get('security.max_login_attempts')) {
            $this->buildCallbackFailMsg('Login Attempts', 'User Max Login Attempts Archived.');
            return;
        }
    }

    public function LoginFail() {
        app()->redis->zAdd('SITE:fail_login_ip_zset', time(), app()->request->getClientIp());
        app()->redis->hIncrBy('SITE:fail_login_ip_count', app()->request->getClientIp(), 1);
    }

    public function createUserSession()
    {
        $userId = $this->self['id'];

        $exist_session_count = app()->redis->zCount($this->sessionSaveKey, $userId, $userId);
        if ($exist_session_count < app()->config->get('base.max_per_user_session')) {
            /**
             * SessionId Format:
             *      /^(?P<secure_login_flag>[01])\$(?P<ip_or_random_crc>[a-z0-9]{8})\$\w+$/
             * The first character of sessionId is the Flag of secure login,
             * if secure login, The second param is the sprintf('%08x',crc32($id))
             *            else, Another random string with length 8
             * The prefix of sessionId is in lowercase
             *
             */
            if ($this->securelogin === 'yes') {
                $sid_prefix = '1$' . sprintf('%08x',crc32(app()->request->getClientIp())) . '$';
            } else {
                $sid_prefix = '0$' . StringHelper::getRandomString(8) . '$';
            }
            $sid_prefix = strtolower($sid_prefix);
            do { // To make sure this session is unique !
                $userSessionId = $sid_prefix . StringHelper::getRandomString($this->sessionLength - strlen($sid_prefix));

                $count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `user_session_log` WHERE sid = :sid')->bindParams([
                    'sid' => $userSessionId
                ])->queryScalar();
            } while ($count != 0);

            // store user login information , ( for example `login ip`,`user_agent`,`last activity at` )
            app()->pdo->createCommand('INSERT INTO `user_session_log`(`uid`, `sid`, `login_ip`, `user_agent` , `last_access_at`) ' .
                'VALUES (:uid,:sid,INET6_ATON(:login_ip),:ua, NOW())')->bindParams([
                'uid' => $userId, 'sid' => $userSessionId,
                'login_ip' => app()->request->getClientIp(),
                'ua' => app()->request->header('user-agent')
            ])->execute();

            // Add this session id in Redis Cache
            app()->redis->zAdd($this->sessionSaveKey, $userId, $userSessionId);

            // Set User Cookie
            $cookieExpire = $this->cookieExpires;
            if ($this->logout === 'yes') {
                $cookieExpire = time() + 15 * 60;
                // TODO Use redis zset to auto clean those session
                app()->redis->zAdd('Site:Sessions:to_expire', $cookieExpire, $userSessionId);
            }

            app()->response->setCookie(Constant::cookie_name, $userSessionId, $cookieExpire, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly);
            return true;
        } else {
            return 'Reach the limit of Max User Session.';
        }
    }

    public function updateUserLoginInfo()
    {
        // TODO or move to task queue...
        app()->pdo->createCommand("UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => $this->self["id"]
        ])->execute();
    }
}
