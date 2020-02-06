<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace App\Models\Form\Auth;

use App\Libraries\Constant;
use App\Entity\User\UserStatus;

use Rid\Helpers\StringHelper;
use Rid\Helpers\JWTHelper;
use Rid\Validators\CaptchaTrait;
use Rid\Validators\Validator;
use Symfony\Component\HttpFoundation\Cookie;

class UserLoginForm extends Validator
{
    use CaptchaTrait;

    public $username;

    public $logout;
    public $securelogin;
    public $ssl;

    private $self;
    private $jwt_payload;

    protected $_autoload = true;
    protected $_autoload_from = ['post'];

    public static function inputRules(): array
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

    public static function callbackRules(): array
    {
        return ['validateCaptcha', 'loadUserFromPdo', 'isMaxUserSessionsReached'];
    }

    /** @noinspection PhpUnused */
    protected function loadUserFromPdo()
    {
        $this->self = app()->pdo->prepare('SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1')->bindParams([
            'uname' => $this->getInput('username'), 'email' => $this->getInput('username'),
        ])->queryOne();

        if (false === $this->self) {  // User is not exist
            /** Notice: We shouldn't tell `This User is not exist in this site.` for user information security. */
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User's password is not correct
        if (!password_verify($this->getInput('password'), $this->self['password'])) {
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // User input 2FA code but not enabled in fact
        if ($this->getInput('opt') && is_null($this->self['opt'])) {
            $this->buildCallbackFailMsg('User', 'Invalid username/password');
            return;
        }

        // FIXME User enable 2FA but it's code is wrong
        /*
        if (!is_null($this->self['opt'])) {
            try {
                $tfa = new TwoFactorAuth(config('base.site_name'));
                if ($tfa->verifyCode($this->self['opt'], $this->getInput('opt')) == false) {
                    $this->buildCallbackFailMsg('2FA', '2FA Validation failed. Check your device time.');
                    return;
                }
            } catch (TwoFactorAuthException $e) {
                $this->buildCallbackFailMsg('2FA', '2FA Validation failed for unknown reason. Tell our support team.');
                return;
            }
        }
        */

        // User 's status is banned or pending~
        if (in_array($this->self['status'], [UserStatus::DISABLED, UserStatus::PENDING])) {
            $this->buildCallbackFailMsg('Account', 'User account is disabled or may not confirmed.');
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function isMaxUserSessionsReached()
    {
        $exist_session_count = app()->pdo->prepare('SELECT COUNT(`id`) FROM sessions WHERE uid = :uid AND expired != 1')->bindParams([
            'uid' => $this->self['id']
        ])->queryScalar();

        if ($exist_session_count >= config('base.max_per_user_session')) {
            $this->buildCallbackFailMsg('max_per_user_session', 'Reach the limit of Max User Session.');
        }
    }

    public function loginFail()
    {
        $user_ip = app()->request->getClientIp();
        $test_attempts = app()->redis->hIncrBy('Site:fail_login_ip_count', $user_ip, 1);
        if ($test_attempts >= config('security.max_login_attempts')) {
            app()->site->banIp($user_ip);
        }
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

        do { // Generate unique JWT ID
            $jti = StringHelper::getRandomString(64);
            $count = app()->pdo->prepare('SELECT COUNT(`id`) FROM sessions WHERE session = :sid;')->bindParams([
                'sid' => $jti
            ])->queryScalar();
        } while ($count != 0);

        // Official Payload key
        $payload = [
            'iss' => config('base.site_url'),
            'sub' => config('base.site_generator'),
            'aud' =>  $this->self['id'],  // Store User Id so we can quick check session status and load their information
            'iat' => $timenow,
            'jti' => $jti,
        ];

        $cookieExpire = 0x7fffffff;  // Tuesday, January 19, 2038 3:14:07 AM (though it is not security)
        if ($this->logout === 'yes' || config('security.auto_logout') > 1) {
            $cookieExpire = $timenow + 15 * 60;  // for 15 minutes
        }
        $payload['exp'] = $cookieExpire;

        // Custom Payload key
        $login_ip = app()->request->getClientIp();
        if ($this->securelogin === 'yes' || config('security.secure_login') > 1) {
            $payload['ip'] = sprintf('%08x', crc32($login_ip));  // Store User Login IP ( in CRC32 format )
        }

        if ($this->ssl || config('security.ssl_login') > 1) {
            $payload['ssl'] = true;
        }  // Store User want full ssl protect

        // Generate JWT content
        $this->jwt_payload = $payload;
        $jwt = JWTHelper::encode($payload);

        // Store User Login Session Information in database
        app()->pdo->prepare('INSERT INTO sessions (`uid`, `session`, `login_ip`, `login_at`, `expired`) ' .
            'VALUES (:uid, :sid, INET6_ATON(:login_ip), NOW(), :expired)')->bindParams([
            'uid' => $this->jwt_payload['aud'], 'sid' => $this->jwt_payload['jti'], 'login_ip' => $login_ip,
            'expired' => ($this->logout === 'yes') ? 0 : -1,  // -1 -> never expired , 0 -> auto_expire after 15 minutes, 1 -> expired
        ])->execute();

        // Sent JWT content AS Cookie
        app()->response->headers->setCookie(new Cookie(Constant::cookie_name, $jwt, $cookieExpire, '/', '', false, true));
    }

    private function updateUserLoginInfo()
    {
        $ip = app()->request->getClientIp();

        // Update User Tables
        app()->pdo->prepare('UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id')->bindParams([
            'ip' => $ip, 'id' => $this->self['id']
        ])->execute();
    }

    private function noticeUser()
    {
        // TODO send email to tail user login
    }
}
