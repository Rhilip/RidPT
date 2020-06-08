<?php

namespace Rid\Http;

use Rid\Redis\Connection;
use Rid\Utils\Random;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Session组件
 */
class Session
{
    // SessionID长度
    protected int $idLength = 26;

    // 服务器保存设置
    protected string $saveKeyPrefix = 'Session:';     // 保存的Key前缀
    protected int $maxLifetime = 7200;     // 生存时间

    // 用户侧Cookies设置
    protected string $cookieName = 'session_id';  // session名
    protected int $cookieExpires = 0;     // 过期时间
    protected string $cookiePath = '/';     // 有效的服务器路径
    protected string $cookieDomain = '';     // 有效域名/子域名
    protected bool $cookieSecure = false;      // 仅通过安全的 HTTPS 连接传给客户端
    protected bool $cookieHttpOnly = false;      // 仅可通过 HTTP 协议访问

    protected Connection $redis;

    public function __construct(Connection $redisConnection)
    {
        $this->redis = $redisConnection;
    }

    // 获取SessionId
    public function getSessionId(): string
    {
        $session_id = container()->get('request')->cookies->get($this->cookieName);
        if (is_null($session_id)) {
            // Generate Unique Session Id
            do {
                $session_id = Random::alnum($this->idLength);
            } while ($this->redis->exists($this->saveKeyPrefix . $session_id));

            // Save it both to request and response
            container()->get('request')->cookies->set($this->cookieName, $session_id);
            container()->get('response')->headers->setCookie(new Cookie($this->cookieName, $session_id, $this->cookieExpires, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly));
        }

        return $session_id;
    }

    public function getSessionKey(): string
    {
        return $this->saveKeyPrefix . $this->getSessionId();
    }

    // 赋值
    public function set($name, $value)
    {
        $success = $this->redis->hMSet($this->getSessionKey(), [$name => $value]);
        $this->redis->expire($this->getSessionKey(), $this->maxLifetime);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $result = $this->redis->hgetall($this->getSessionKey());
            return $result ?: [];
        }
        $value = $this->redis->hget($this->getSessionKey(), $name);
        return $value === false ? null : $value;
    }

    public function has($name): bool
    {
        return (bool)$this->redis->hexists($this->getSessionKey(), $name);
    }

    // 删除
    public function delete($name)
    {
        $success = $this->redis->hdel($this->getSessionKey(), $name);
        return $success ? true : false;
    }

    // 取值后删除
    public function pop($name)
    {
        $value = $this->get($name);
        $this->delete($name);
        return $value;
    }

    // 清除session
    public function clear()
    {
        $success = $this->redis->del($this->getSessionKey());
        return $success ? true : false;
    }

    // FIXME
    public function setCsrfToken()
    {
        $csrf = Random::alnum(16);
        $this->set('csrf', $csrf);
        return $csrf;
    }
}
