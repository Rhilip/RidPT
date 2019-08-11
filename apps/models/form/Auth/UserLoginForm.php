<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace apps\models\form\Auth;

use apps\libraries\Constant;
use apps\models\User;

use Rid\Helpers\StringHelper;
use Rid\Helpers\JWTHelper;
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

    // Cookie
    private $cookieExpires = 0x7fffffff;
    private $cookiePath = '/';
    private $cookieDomain = '';
    private $cookieSecure = false;  // Notice : Only change this value when you first run !!!!
    private $cookieHttpOnly = true;

    private $jwt_payload;

    protected $_autoload_data = true;
    protected $_autoload_data_from = ['post'];

    public static function inputRules()
    {
        /**
         * We only control frontend behaviour of input keys - `securelogin`, `logout`, `ssl`,
         * So we not add Rules `Required` for these keys
         */
        return [
            'username' => 'Required',
            'password' => [
                ['Required'],
                ['Length', ['min' => 6, 'max' => 40]]
            ],
            'opt' => [
                ['Length', ['max' => 6]]
            ],
            'securelogin' => 'Equal(value=yes)',
            'logout' => 'Equal(value=yes)',
            'ssl' => 'Equal(value=yes)',
        ];
    }

    public static function callbackRules()
    {
        return ['validateCaptcha', 'isMaxLoginIpReached', 'loadUserFromPdo', 'isMaxUserSessionsReached'];
    }

    /** @noinspection PhpUnused */
    protected function isMaxLoginIpReached()  // FIXME may use Trait
    {
        $test_count = app()->redis->hGet('SITE:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
        if ($test_count > config('security.max_login_attempts')) {
            $this->buildCallbackFailMsg('Login Attempts', 'User Max Login Attempts Archived.');
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function loadUserFromPdo()
    {
        $this->self = app()->pdo->createCommand('SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1')->bindParams([
            'uname' => $this->getData('username'), 'email' => $this->getData('username'),
        ])->queryOne();

        if (false === $this->self) {  // User is not exist
            /** Notice: We shouldn't tell `This User is not exist in this site.` for user information security. */
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User's password is not correct
        if (!password_verify($this->getData('password'), $this->self['password'])) {
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User input 2FA code but not enabled in fact
        if ($this->getData('opt') && is_null($this->self['opt'])) {
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User enable 2FA but it's code is wrong
        if (!is_null($this->self['opt'])) {
            try {
                $tfa = new TwoFactorAuth(config('base.site_name'));
                if ($tfa->verifyCode($this->self['opt'], $this->getData('opt')) == false) {
                    $this->buildCallbackFailMsg('2FA', '2FA Validation failed. Check your device time.');
                    return;
                }
            } catch (TwoFactorAuthException $e) {
                $this->buildCallbackFailMsg('2FA', '2FA Validation failed for unknown reason. Tell our support team.');
                return;
            }
        }

        // User 's status is banned or pending~
        if (in_array($this->self['status'], [User::STATUS_DISABLED, User::STATUS_PENDING])) {
            $this->buildCallbackFailMsg('Account', 'User account is disabled or may not confirmed.');
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function isMaxUserSessionsReached()
    {
        $exist_session_count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `user_session_log` WHERE uid = :uid AND expired = 0')->bindParams([
            'uid' => $this->self['id']
        ])->queryScalar();

        if ($exist_session_count >= config('base.max_per_user_session')) {
            $this->buildCallbackFailMsg('max_per_user_session', 'Reach the limit of Max User Session.');
        }
    }

    public function LoginFail()
    {
        app()->redis->zAdd('SITE:fail_login_ip_zset', time(), app()->request->getClientIp());
        app()->redis->hIncrBy('SITE:fail_login_ip_count', app()->request->getClientIp(), 1);
    }

    public function flush()
    {
        $this->createUserSession();
        $this->updateUserLoginInfo();
        $this->noticeUser();
    }

    /**
     * Use jwt ways to generate user identity
     */
    private function createUserSession()
    {
        $timenow = time();
        $login_ip = app()->request->getClientIp();

        do { // Generate unique JWT ID
            $jti = StringHelper::getRandomString(64);
            $count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `user_session_log` WHERE sid = :sid;')->bindParams([
                'sid' => $jti
            ])->queryScalar();
        } while ($count != 0);

        // Official Payload key
        $payload = [
            'iss' => config('base.site_url'),
            'iat' => $timenow,
            'jti' => $jti,
        ];

        $cookieExpire = $this->cookieExpires;
        if ($this->logout === 'yes') {
            $payload['exp'] = $cookieExpire = $timenow + 15 * 60;  // 15 minutes
        }

        // Custom Payload key
        $payload['user_id'] = $this->self['id'];  // Store User Id so we can quick load their information
        if ($this->securelogin === 'yes') $payload['secure_login_ip'] = sprintf('%08x', crc32($login_ip));  // Store User Login IP ( in CRC32 format )
        if ($this->ssl) $payload['ssl'] = true;  // FIXME Check if site support this feature , Store User want full ssl protect

        // Generate JWT content
        $this->jwt_payload = $payload;
        $jwt = JWTHelper::encode($payload);

        // Sent JWT content AS Cookie
        app()->response->setCookie(Constant::cookie_name, $jwt, $cookieExpire, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly);
    }

    private function updateUserLoginInfo()
    {
        $ip = app()->request->getClientIp();

        // Store User Login Session Information in database
        app()->pdo->createCommand('INSERT INTO `user_session_log`(`uid`, `sid`, `login_ip`, `user_agent` ,`login_at`, `last_access_at`,`expired`) ' .
            'VALUES (:uid, :sid, INET6_ATON(:login_ip), :ua, NOW(), NOW(), :expired)')->bindParams([
            'uid' => $this->jwt_payload['user_id'], 'sid' => $this->jwt_payload['jti'],
            'login_ip' => $ip, 'ua' => app()->request->header('user-agent'),
            'expired' => ($this->logout === 'yes') ? 0 : -1,  // -1 -> never expired , 0 -> auto_expire after 15 minutes, 1 -> expired
        ])->execute();

        // Update User Tables
        app()->pdo->createCommand('UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id')->bindParams([
            'ip' => $ip, 'id' => $this->self['id']
        ])->execute();
    }

    private function noticeUser()
    {
        // TODO send email to tail user login
    }
}
