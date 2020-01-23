<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/29
 * Time: 10:37
 */

namespace Rid\Utils;

use Symfony\Component\HttpFoundation\IpUtils as HttpFoundationIpUtils;

/**
 * Http utility functions.
 */
class IpUtils extends HttpFoundationIpUtils
{

    /**
     * @param $raw_ip
     * @return array|bool
     */
    public static function isEndPoint($raw_ip)
    {
        preg_match("/\[?([^\]]+)\]?:([0-9]+)/", $raw_ip, $data);

        if ($data) {
            $ip = filter_var($data[1], FILTER_VALIDATE_IP | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
            $port = $data[2];
            if ($ip && $port) {
                return ["ip" => $ip, "port" => $port];
            }
        }
        return false;
    }

    public static function isValidIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function isValidIPv4($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    public static function isValidIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    public static function isPublicIPv4($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public static function isPublicIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
