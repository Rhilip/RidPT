<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/18
 * Time: 21:55
 */

namespace apps\middleware;


use Mix\Utils\IpUtils;

class IpBanMiddleware
{
    public function handle($callable, \Closure $next)
    {
        $ip = app()->request->getClientIp();

        $ip_ban_list = app()->redis->get("SILE:ip_ban_list");
        if ($ip_ban_list === false) {
            $ip_ban_list = app()->pdo->createCommand("SELECT `ip` FROM `ip_bans`")->queryAll();
            app()->redis->set("SILE:ip_ban_list", $ip_ban_list);
        }

        if (count($ip_ban_list) > 0 && IpUtils::checkIp($ip, $ip_ban_list)) {
            return app()->response->setStatusCode(403);
        }

        return $next();
    }
}
