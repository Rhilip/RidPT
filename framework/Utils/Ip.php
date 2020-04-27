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
class Ip extends HttpFoundationIpUtils
{

    /**
     * @param $raw_ip
     * @return array|bool
     */
    public static function isEndPoint($raw_ip)
    {
        preg_match("/\[?([^\]]+)\]?:([0-9]+)/", $raw_ip, $data);

        if ($data) {
            $ip = self::isPublicIp($data[1]);
            $port = $data[2];
            if ($ip && $port) {
                return ['ip' => $ip, 'port' => $port];
            }
        }
        return false;
    }

    public static function isValidIP($ip, $flags = null)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }

    public static function isValidIPv4($ip)
    {
        return self::isValidIP($ip, FILTER_FLAG_IPV4);
    }

    public static function isValidIPv6($ip)
    {
        return self::isValidIP($ip, FILTER_FLAG_IPV6);
    }

    public static function isPublicIp($ip, $flags = null)
    {
        /**
         * FILTER_FLAG_NO_PRIV_RANGE:
         *   - Fails validation for the following private IPv4 ranges: 10.0.0.0/8, 172.16.0.0/12 and 192.168.0.0/16.
         *   - Fails validation for the IPv6 addresses starting with FD or FC.
         * FILTER_FLAG_NO_RES_RANGE:
         *   - Fails validation for the following reserved IPv4 ranges: 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8 and 240.0.0.0/4.
         *   - Fails validation for the following reserved IPv6 ranges: ::1/128, ::/128, ::ffff:0:0/96 and fe80::/10.
         */
        return self::isValidIP($ip, $flags | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    public static function isPublicIPv4($ip)
    {
        return self::isPublicIp($ip, FILTER_FLAG_IPV4);
    }

    public static function isPublicIPv6($ip)
    {
        return self::isPublicIp($ip, FILTER_FLAG_IPV6);
    }
}
