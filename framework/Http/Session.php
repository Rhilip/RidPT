<?php

namespace Rid\Http;

use Rid\Base\Component;
use Rid\Helpers\StringHelper;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Session组件
 */
class Session extends Component
{
    // 保存的Key前缀
    public $saveKeyPrefix = 'Session:';

    // 生存时间
    public $maxLifetime = 7200;

    // session名
    public $name = 'session_id';

    // 过期时间
    public $cookieExpires = 0;

    // 有效的服务器路径
    public $cookiePath = '/';

    // 有效域名/子域名
    public $cookieDomain = '';

    // 仅通过安全的 HTTPS 连接传给客户端
    public $cookieSecure = false;

    // 仅可通过 HTTP 协议访问
    public $cookieHttpOnly = false;

    // SessionID长度
    protected $_sessionIdLength = 26;

    // 获取SessionId
    public function getSessionId(): string
    {
        $session_id = \Rid::app()->request->cookies->get($this->name);
        if (is_null($session_id)) {
            // Generate Unique Session Id
            do {
                $session_id = StringHelper::getRandomString($this->_sessionIdLength);
            } while (app()->redis->exists($this->saveKeyPrefix . $session_id));

            // Save it both to request and response
            app()->request->cookies->set($this->name, $session_id);
            \Rid::app()->response->headers->setCookie(new Cookie($this->name, $session_id, $this->cookieExpires, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly));
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
        $success = app()->redis->hMSet($this->getSessionKey(), [$name => $value]);
        app()->redis->expire($this->getSessionKey(), $this->maxLifetime);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $result = app()->redis->hgetall($this->getSessionKey());
            return $result ?: [];
        }
        $value = app()->redis->hget($this->getSessionKey(), $name);
        return $value === false ? null : $value;
    }

    public function has($name): bool
    {
        return (bool)app()->redis->hexists($this->getSessionKey(), $name);
    }

    // 删除
    public function delete($name)
    {
        $success = app()->redis->hdel($this->getSessionKey(), $name);
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
        $success = app()->redis->del($this->getSessionKey());
        return $success ? true : false;
    }

    public function setCsrfToken()
    {
        $csrf = StringHelper::getRandomString(16);
        $this->set('csrf', $csrf);
        return $csrf;
    }
}
