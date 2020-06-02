<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace App\Components;

use App\Entity;
use App\Libraries\Constant;
use App\Enums\User\Status as UserStatus;
use Rid\Libraries\JWT;

class Auth
{

    /**
     * @param string $grant
     * @param string|bool $flush
     * @return Entity\User\User|bool return False means this user is anonymous
     */
    public function getCurUser($grant = 'cookies', $flush = false)
    {
        $cur_user = container()->get('request')->attributes->get('cur_user');
        if (is_null($cur_user) || $flush) {
            container()->get('request')->attributes->set('auth_by', $grant);
            $cur_user = $this->loadCurUser($grant);
            container()->get('request')->attributes->set('cur_user', $cur_user);
        }
        return $cur_user;
    }

    public function getCurUserJIT(): string
    {
        $jwt = container()->get('request')->attributes->get('jwt') ?? [];
        return $jwt['jti'] ?? '';
    }

    public function getGrant(): string
    {
        return container()->get('request')->attributes->get('auth_by');
    }

    /**
     * @param string $grant
     * @return Entity\User\User|boolean
     */
    protected function loadCurUser($grant = 'cookies')
    {
        $user_id = false;
        if ($grant == 'cookies') {
            $user_id = $this->loadCurUserIdFromCookies();
        } elseif ($grant == 'passkey') {
            $user_id = $this->loadCurUserIdFromPasskey();
        }

        if ($user_id !== false && is_int($user_id) && $user_id > 0) {
            $user_id = (int)$user_id;
            $curuser = container()->get(\App\Entity\User\UserFactory::class)->getUserById($user_id);
            if ($curuser->getStatus() !== UserStatus::DISABLED) {  // user status shouldn't be disabled
                return $curuser;
            }
        }

        return false;
    }

    protected function loadCurUserIdFromCookies()
    {
        $user_session = container()->get('request')->cookies->get(Constant::cookie_name);
        // quick return when cookies is not exist
        if (is_null($user_session)) {
            return false;
        }

        // quick return when decode JWT session failed
        if (false === $payload = container()->get(JWT::class)->decode($user_session)) {
            return false;
        }
        if (!isset($payload['jti']) || !isset($payload['aud'])) {
            return false;
        }

        // Check if user lock access ip ?
        if (isset($payload['ip'])) {
            $now_ip_crc = sprintf('%08x', crc32(container()->get('request')->getClientIp()));
            if (strcasecmp($payload['ip'], $now_ip_crc) !== 0) {
                return false;
            }
        }

        // Verity $payload['jti'] is force expired or not, And check if it same with $payload['aud']
        // And if not match, it means user logout this session or change password....
        $uid = container()->get(Entity\User\UserFactory::class)->getUserIdBySession($payload['jti']);
        if ($uid != $payload['aud']) {
            return false;
        }

        container()->get('request')->attributes->set('jwt', $payload);

        // Check if user want secure access but his environment is not secure
        if (!container()->get('request')->isSecure() &&                        // if User requests is not secure , and
            (
                config('security.ssl_login') > 1       // if Our site FORCE enabled ssl feature
                || (config('security.ssl_login') > 0 && isset($payload['ssl']) && $payload['ssl']) // if Our site support ssl feature and User want secure access
            )
        ) {
            container()->get('response')->setRedirect(str_replace('http://', 'https://', container()->get('request')->getUri()));
            container()->get('response')->headers->set('Strict-Transport-Security', 'max-age=1296000; includeSubDomains');
        }

        return $payload['aud'];
    }

    protected function loadCurUserIdFromPasskey()
    {
        $passkey = container()->get('request')->query->get('passkey');
        if (is_null($passkey)) {
            return false;
        }

        $user_id = container()->get(Entity\User\UserFactory::class)->getUserIdByPasskey($passkey);
        return $user_id > 0 ? $user_id : false;
    }

    private function logSessionInfo()
    {
        if (!is_null($this->getCurUserJIT()) && $this->getCurUser()) {
            $uid = $this->getCurUser()->getId();
            $now_ip = container()->get('request')->getClientIp();
            $ua = container()->get('request')->headers->get('user-agent');

            $identify_key = md5(implode('|', [
                $this->getCurUserJIT(),  // `sessions`->session
                $now_ip /* user ip */, $ua  /* user agent */
            ]));

            // Update User access info by HyperLogLog
            $grain_size = date('YmdH') ; // per hour
            // $grain_size = date('YmdH') . floor(date('i') / 15);  // per 15 minutes
            $check = container()->get('redis')->pfAdd('Site:hyperloglog:access_log_' . $grain_size, [$identify_key]);
            if ($check == 1) {
                // Update Table `users`
                container()->get('pdo')->prepare('UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id;')->bindParams([
                    'ip' => $now_ip, 'id' => $uid
                ])->execute();

                // Insert Table `session_log`
                container()->get('pdo')->prepare('INSERT INTO `session_log` (`sid`, `access_at`, `access_ip`, `user_agent`) VALUES ((SELECT `id` FROM `sessions` WHERE `session` = :jit), NOW(), INET6_ATON(:access_ip), :ua)')->bindParams([
                    'jit' => $this->getCurUserJIT(), 'access_ip' => $now_ip, 'ua' => $ua
                ])->execute();
            }
        }
    }
}
