<?php

namespace Mix\Http;

use Mix\Base\Component;

use Mix\Utils\HeaderUtils;
use Mix\Utils\IpUtils;

use DeviceDetector\DeviceDetector;

/**
 * Request组件基类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseRequest extends Component
{

    const HEADER_FORWARDED = 0b00001; // When using RFC 7239
    const HEADER_X_FORWARDED_FOR = 0b00010;
    const HEADER_X_FORWARDED_HOST = 0b00100;
    const HEADER_X_FORWARDED_PROTO = 0b01000;
    const HEADER_X_FORWARDED_PORT = 0b10000;
    const HEADER_X_FORWARDED_ALL = 0b11110; // All "X-Forwarded-*" headers
    const HEADER_X_FORWARDED_AWS_ELB = 0b11010; // AWS ELB doesn't send X-Forwarded-Host

    private $isForwardedValid = true;

    private static $forwardedParams = array(
        self::HEADER_X_FORWARDED_FOR => 'for',
        self::HEADER_X_FORWARDED_HOST => 'host',
        self::HEADER_X_FORWARDED_PROTO => 'proto',
        self::HEADER_X_FORWARDED_PORT => 'host',
    );

    /**
     * Names for headers that can be trusted when
     * using trusted proxies.
     *
     * The FORWARDED header is the standard as of rfc7239.
     *
     * The other headers are non-standard, but widely used
     * by popular reverse proxies (like Apache mod_proxy or Amazon EC2).
     */
    private static $trustedHeaders = array(
        self::HEADER_FORWARDED => 'forwarded',
        self::HEADER_X_FORWARDED_FOR => 'x-forwarded-for',
        self::HEADER_X_FORWARDED_HOST => 'x-forwarded-host',
        self::HEADER_X_FORWARDED_PROTO => 'x-forwarded-proto',
        self::HEADER_X_FORWARDED_PORT => 'x-forwarded-port',
    );

    // ROUTE 参数
    protected $_route = [];

    // GET 参数
    protected $_get = [];

    // POST 参数
    protected $_post = [];

    // FILES 参数
    protected $_files = [];

    // COOKIE 参数
    protected $_cookie = [];

    // SERVER 参数
    protected $_server = [];

    // HEADER 参数
    protected $_header = [];

    /**
     * @var string[]
     */
    protected static $trustedProxies = array('127.0.0.1','::1');

    private static $trustedHeaderSet = -1;

    // 设置 ROUTE 值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    // 提取 GET 值
    public function get($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_get);
    }

    // 提取 POST 值
    public function post($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_post);
    }

    // 提取 FILES 值
    public function files($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_files);
    }

    // 提取 ROUTE 值
    public function route($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_route);
    }

    // 提取 COOKIE 值
    public function cookie($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_cookie);
    }

    // 提取 SERVER 值
    public function server($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_server);
    }

    // 提取 HEADER 值
    public function header($name = null, $default = null)
    {
        return self::fetch($name, $default, $this->_header);
    }

    // 提取数据
    protected static function fetch($name, $default, $container)
    {
        return is_null($name) ? $container : (isset($container[$name]) ? $container[$name] : $default);
    }

    // 是否为 GET 请求
    public function isGet()
    {
        return $this->method() == 'GET';
    }

    // 是否为 POST 请求
    public function isPost()
    {
        return $this->method() == 'POST';
    }

    // 是否为 PUT 请求
    public function isPut()
    {
        return $this->method() == 'PUT';
    }

    // 是否为 PATCH 请求
    public function isPatch()
    {
        return $this->method() == 'PATCH';
    }

    // 是否为 DELETE 请求
    public function isDelete()
    {
        return $this->method() == 'DELETE';
    }

    // 是否为 HEAD 请求
    public function isHead()
    {
        return $this->method() == 'HEAD';
    }

    // 是否为 OPTIONS 请求
    public function isOptions()
    {
        return $this->method() == 'OPTIONS';
    }

    // 返回请求类型
    public function method()
    {
        return $this->server('request_method');
    }

    // 返回请求的根URL
    public function root()
    {
        return $this->scheme() . '://' . $this->header('host');
    }

    // 返回请求的路径
    public function path()
    {
        return substr($this->server('path_info'), 1);
    }

    // 返回请求的URL
    public function url()
    {
        return $this->scheme() . '://' . $this->header('host') . $this->server('path_info');
    }

    // 返回请求的完整URL
    public function fullUrl()
    {
        return $this->scheme() . '://' . $this->header('host') . $this->server('path_info') . '?' . $this->server('query_string');
    }

    // 获取协议
    protected function scheme()
    {
        return $this->server('request_scheme') ?: $this->header('scheme');
    }

    /**
     * Return the client Raw User-Agent
     *
     * This method can read the client User Agent info form the "User-Agent" header
     * when set `$detector=true` ,it will return the DeviceDetector object to
     * help user quick detects devices (desktop, tablet, mobile, tv, cars, console, etc.),
     * clients (browsers, feed readers, media players, PIMs, ...), operating systems,
     * brands and models.
     *
     * @see https://github.com/matomo-org/device-detector
     *
     * @param bool $detector
     * @return DeviceDetector|string
     */
    public function getUserAgent($detector = false)
    {
        $userAgent = $this->header('user-agent') ?? '';
        if ($detector) {
            $dd = new DeviceDetector($userAgent);
            $dd->parse();
            return $dd;
        }
        return $userAgent;
    }

    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see http://en.wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp() {
        $ipAddresses = $this->getClientIps();
        return $ipAddresses[0];
    }

    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps()
    {
        $ip = $this->server('remote_addr');

        if (!$this->isFromTrustedProxy()) {
            return array($ip);
        }

        return $this->getTrustedValues(self::HEADER_X_FORWARDED_FOR, $ip) ?: array($ip);

    }

    public function isFromTrustedProxy()
    {
        return self::$trustedProxies && IpUtils::checkIp($this->server('remote_addr'), self::$trustedProxies);
    }

    /**
     * @param $type
     * @param null $ip
     * @return array
     */
    private function getTrustedValues($type, $ip = null)
    {
        $clientValues = array();
        $forwardedValues = array();
        if ((self::$trustedHeaderSet & $type) && $this->header(self::$trustedHeaders[$type])) {
            foreach (explode(',', $this->header(self::$trustedHeaders[$type])) as $v) {
                $clientValues[] = (self::HEADER_X_FORWARDED_PORT === $type ? '0.0.0.0:' : '').trim($v);
            }
        }
        if ((self::$trustedHeaderSet & self::HEADER_FORWARDED) && $this->header(self::$trustedHeaders[self::HEADER_FORWARDED])) {
            $forwarded = $this->header(self::$trustedHeaders[self::HEADER_FORWARDED]);
            $parts = HeaderUtils::split($forwarded, ',;=');
            $forwardedValues = array();
            $param = self::$forwardedParams[$type];
            foreach ($parts as $subParts) {
                if (null === $v = HeaderUtils::combine($subParts)[$param] ?? null) {
                    continue;
                }
                if (self::HEADER_X_FORWARDED_PORT === $type) {
                    if (']' === substr($v, -1) || false === $v = strrchr($v, ':')) {
                        $v = $this->isSecure() ? ':443' : ':80';
                    }
                    $v = '0.0.0.0'.$v;
                }
                $forwardedValues[] = $v;
            }
        }
        if (null !== $ip) {
            $clientValues = $this->normalizeAndFilterClientIps($clientValues, $ip);
            $forwardedValues = $this->normalizeAndFilterClientIps($forwardedValues, $ip);
        }
        if ($forwardedValues === $clientValues || !$clientValues) {
            return $forwardedValues;
        }
        if (!$forwardedValues) {
            return $clientValues;
        }
        $this->isForwardedValid = false;
        return null !== $ip ? array('0.0.0.0', $ip) : array();
    }

    public function isSecure()
    {
        if ($this->isFromTrustedProxy() && $proto = $this->getTrustedValues(self::HEADER_X_FORWARDED_PROTO)) {
            return \in_array(strtolower($proto[0]), array('https', 'on', 'ssl', '1'), true);
        }
        $https = $this->server('https');
        return !empty($https) && 'off' !== strtolower($https);
    }

    private function normalizeAndFilterClientIps(array $clientIps, $ip)
    {
        if (!$clientIps) {
            return array();
        }
        $clientIps[] = $ip; // Complete the IP chain with the IP the request actually came from
        $firstTrustedIp = null;
        foreach ($clientIps as $key => $clientIp) {
            if (strpos($clientIp, '.')) {
                // Strip :port from IPv4 addresses. This is allowed in Forwarded
                // and may occur in X-Forwarded-For.
                $i = strpos($clientIp, ':');
                if ($i) {
                    $clientIps[$key] = $clientIp = substr($clientIp, 0, $i);
                }
            } elseif (0 === strpos($clientIp, '[')) {
                // Strip brackets and :port from IPv6 addresses.
                $i = strpos($clientIp, ']', 1);
                $clientIps[$key] = $clientIp = substr($clientIp, 1, $i - 1);
            }
            if (!filter_var($clientIp, FILTER_VALIDATE_IP)) {
                unset($clientIps[$key]);
                continue;
            }
            if (IpUtils::checkIp($clientIp, self::$trustedProxies)) {
                unset($clientIps[$key]);
                // Fallback to this when the client IP falls into the range of trusted proxies
                if (null === $firstTrustedIp) {
                    $firstTrustedIp = $clientIp;
                }
            }
        }
        // Now the IP chain contains only untrusted proxies and the client IP
        return $clientIps ? array_reverse($clientIps) : array($firstTrustedIp);
    }
}
