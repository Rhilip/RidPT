<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/18
 * Time: 21:55
 */

namespace apps\middleware;

use Rid\Utils\IpUtils;

class IpBanMiddleware
{
    /** @noinspection PhpUnused */
    public function handle($callable, \Closure $next)
    {
        $ip = app()->request->getClientIp();
        $ip_ban_list = $this->getBanIpsList();

        if (count($ip_ban_list) > 0 && IpUtils::checkIp($ip, $ip_ban_list)) {
            return app()->response->setStatusCode(403);
        }

        return $next();
    }

    private function getBanIpsList(): array
    {
        $timenow = time();
        $ban_ips_check = config('runtime.ban_ips_list_check');
        if ($ban_ips_check === false  // Init to avoid Redis Cache not exist
            || $ban_ips_check > $timenow + 86400  // Keep Redis Cache For 1 days
        ) {
            $ban_ips = app()->pdo->createCommand('SELECT `ip` FROM `ban_ips`')->queryColumn() ?: [];
            app()->redis->sAdd('Site:ban_ips_list', ...$ban_ips);
            app()->config->set('runtime.ban_ips_list_check', $timenow, 'int');
        } else {
            $ban_ips = app()->redis->sMembers('Site:ban_ips_list');
        }

        return $ban_ips;
    }
}
