<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/18
 * Time: 21:55
 */

namespace App\Middleware;

use Rid\Utils\IpUtils;

class IpBanMiddleware
{
    /** @noinspection PhpUnused */
    public function handle($callable, \Closure $next)
    {
        $ip = app()->request->getClientIp();
        $ip_ban_list = app()->site->getBanIpsList();

        if (count($ip_ban_list) > 0 && IpUtils::checkIp($ip, $ip_ban_list)) {
            return app()->response->setStatusCode(403);
        }

        return $next();
    }
}
