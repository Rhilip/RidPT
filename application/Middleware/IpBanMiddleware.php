<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/18
 * Time: 21:55
 */

namespace App\Middleware;

use Rid\Http\Middleware\AbstractMiddleware;
use Rid\Utils\Ip;

class IpBanMiddleware extends AbstractMiddleware
{
    public function handle($callable, \Closure $next)
    {
        $ip = \Rid\Helpers\ContainerHelper::getContainer()->get('request')->getClientIp();
        $ip_ban_list = \Rid\Helpers\ContainerHelper::getContainer()->get('site')->getBanIpsList();

        if (count($ip_ban_list) > 0 && Ip::checkIp($ip, $ip_ban_list)) {
            return \Rid\Helpers\ContainerHelper::getContainer()->get('response')->setStatusCode(403);
        }

        return $next();
    }
}
