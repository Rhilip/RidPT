<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth;

use App\Enums\User\Status as UserStatus;
use App\Forms\Traits\CaptchaTrait;
use App\Libraries\Constant;
use Rid\Libraries\JWT;
use Rid\Utils\Random;
use Rid\Validators\AbstractValidator;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Validator\Constraints as Assert;

class LoginForm extends AbstractValidator
{
    use CaptchaTrait;

    private ?array $self;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'username' => new Assert\NotBlank(),
            'password' => new Assert\Length(['min' => 6, 'max' => 40]),
            'opt' => new Assert\Optional(
                new Assert\Length(['value' => 6, 'allowEmptyString' => true])
            ),  // 2FA code
            'securelogin' => new Assert\Optional(new Assert\IsTrue()),
            'logout' => new Assert\Optional(new Assert\IsTrue()),
            'ssl' => new Assert\Optional(new Assert\IsTrue())
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['validateCaptcha', 'loadUserFromPdo', 'isMaxUserSessionsReached'];
    }

    /** @noinspection PhpUnused */
    protected function loadUserFromPdo()
    {
        $user_record = container()->get('pdo')->prepare('SELECT `id`, `username`, `password`, `status`, `opt`, `class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1')->bindParams([
            'uname' => $this->getInput('username'), 'email' => $this->getInput('username'),
        ])->queryOne();

        if (false === $user_record) {  // User is not exist
            /** Notice: We shouldn't tell `This User is not exist in this site.` for user information security. */
            $this->buildCallbackFailMsg('Account', 'Invalid username/password');
            return;
        }

        // User's password is not correct
        if (!password_verify($this->getInput('password'), $user_record['password'])) {
            $this->buildCallbackFailMsg('Account', 'Invalid username/password');
            return;
        }

        // User input 2FA code or opt field in User Record is not null
        if ($this->getInput('opt') || !is_null($user_record['opt'])) {
            // TODO 2FA check
        }

        // User 's status is banned or pending~
        if (in_array($user_record['status'], [UserStatus::DISABLED, UserStatus::PENDING])) {
            $this->buildCallbackFailMsg('Account', 'Your account is disabled or may not confirmed.');
            return;
        }

        $this->self = $user_record;
    }

    /** @noinspection PhpUnused */
    protected function isMaxUserSessionsReached()
    {
        if (config('base.max_per_user_session') > 0) {
            $exist_session_count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM sessions WHERE uid = :uid AND expired != 1')->bindParams([
                'uid' => $this->self['id']
            ])->queryScalar();

            if ($exist_session_count >= config('base.max_per_user_session')) {
                $this->buildCallbackFailMsg('Session', 'Reach the limit of Max User Session.');
            }
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
            $jti = Random::alnum(64);
            $count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM sessions WHERE session = :sid;')->bindParams([
                'sid' => $jti
            ])->queryScalar();
        } while ($count != 0);

        /** Official Payload key */
        $payload = [
            'iss' => config('base.site_url'),
            'sub' => config('base.site_generator'),
            'aud' => $this->self['id'],  // Store User Id so we can quick check session status and load their information
            'iat' => $timenow,
            'jti' => $jti,
        ];

        $cookieExpire = 0x7fffffff;  // Tuesday, January 19, 2038 3:14:07 AM (though it is not security)
        if ($this->getInput('logout') === 'yes' || config('security.auto_logout') > 1) {
            $cookieExpire = $timenow + 15 * 60;  // for 15 minutes
        }
        $payload['exp'] = $cookieExpire;

        /** Custom Payload key */

        // Store Ip if it's secure login
        $login_ip = container()->get('request')->getClientIp();
        if ((bool)$this->getInput('securelogin') || config('security.secure_login') > 1) {
            $payload['ip'] = sprintf('%08x', crc32($login_ip));  // Store User Login IP ( in CRC32 format )
        }

        // Store ssl if User want full ssl protect
        if ((bool)$this->getInput('ssl') || config('security.ssl_login') > 1) {
            $payload['ssl'] = true;
        }

        // Generate JWT content and sent As Cookie
        $jwt = container()->get(JWT::class)->encode($payload);
        container()->get('response')->headers->setCookie(new Cookie(Constant::cookie_name, $jwt, $cookieExpire, '/', '', false, true));

        // Store User Login Session Information in database
        container()->get('pdo')->prepare('INSERT INTO sessions (`uid`, `session`, `login_ip`, `login_at`, `expired`) ' .
            'VALUES (:uid, :sid, INET6_ATON(:login_ip), NOW(), :expired)')->bindParams([
            'uid' => $payload['aud'], 'sid' => $payload['jti'], 'login_ip' => $login_ip,
            'expired' => (bool)$this->getInput('logout') ? 0 : -1,  // -1 -> never expired , 0 -> auto_expire after 15 minutes, 1 -> expired
        ])->execute();
    }

    private function updateUserLoginInfo()
    {
        $ip = container()->get('request')->getClientIp();

        // Update User Tables
        container()->get('pdo')->prepare('UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id')->bindParams([
            'ip' => $ip, 'id' => $this->self['id']
        ])->execute();
    }

    private function noticeUser()
    {
        // TODO send email to tail user login
    }
}
