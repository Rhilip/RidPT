<?php

namespace Rid\Http;

use Rid\Base\Component;

use Rid\Utils\Random;
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
                $session_id = Random::alnum($this->_sessionIdLength);
            } while (\Rid\Helpers\ContainerHelper::getContainer()->get('redis')->exists($this->saveKeyPrefix . $session_id));

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
        $success = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hMSet($this->getSessionKey(), [$name => $value]);
        \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->expire($this->getSessionKey(), $this->maxLifetime);
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $result = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hgetall($this->getSessionKey());
            return $result ?: [];
        }
        $value = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hget($this->getSessionKey(), $name);
        return $value === false ? null : $value;
    }

    public function has($name): bool
    {
        return (bool)\Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hexists($this->getSessionKey(), $name);
    }

    // 删除
    public function delete($name)
    {
        $success = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->hdel($this->getSessionKey(), $name);
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
        $success = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->del($this->getSessionKey());
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
