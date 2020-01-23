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

    // SessionKey
    protected $_sessionKey;

    // SessionID
    protected $_sessionId;

    // SessionID长度
    protected $_sessionIdLength = 26;

    protected $_isNewSession = false;

    // 请求前置事件
    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->_isNewSession = false;
        $this->loadSessionId();  // 载入session_id
    }

    // 请求后置事件
    public function onRequestAfter()
    {
        parent::onRequestAfter();
        // 关闭连接
        app()->redis->disconnect();
    }

    // 载入session_id
    public function loadSessionId()
    {
        $this->_sessionId = \Rid::app()->request->cookies->get($this->name);
        if (is_null($this->_sessionId)) {
            $this->_isNewSession = true;
            $this->_sessionId = StringHelper::getRandomString($this->_sessionIdLength);
        }
        $this->_sessionKey = $this->saveKeyPrefix . $this->_sessionId;

        if (!$this->_isNewSession) {
            app()->redis->expire($this->_sessionKey, $this->maxLifetime);
        } // 延长session有效期
    }

    // 创建SessionId
    public function createSessionId()
    {
        do {
            $this->_sessionId  = StringHelper::getRandomString($this->_sessionIdLength);
            $this->_sessionKey = $this->saveKeyPrefix . $this->_sessionId;
        } while (app()->redis->exists($this->_sessionKey));
    }

    // 赋值
    public function set($name, $value)
    {
        $success = app()->redis->hMSet($this->_sessionKey, [$name => $value]);
        app()->redis->expire($this->_sessionKey, $this->maxLifetime);
        $success and $this->_isNewSession and \Rid::app()->response->headers->setCookie(new Cookie($this->name, $this->_sessionId, $this->cookieExpires, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly));
        return $success ? true : false;
    }

    // 取值
    public function get($name = null)
    {
        if (is_null($name)) {
            $result = app()->redis->hgetall($this->_sessionKey);
            return $result ?: [];
        }
        $value = app()->redis->hget($this->_sessionKey, $name);
        return $value === false ? null : $value;
    }

    // 判断是否存在
    public function has($name)
    {
        $exist = app()->redis->hexists($this->_sessionKey, $name);
        return $exist ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = app()->redis->hdel($this->_sessionKey, $name);
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
        $success = app()->redis->del($this->_sessionKey);
        return $success ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_sessionId;
    }

    public function setCsrfToken()
    {
        $csrf = StringHelper::getRandomString(16);
        $this->set('csrf', $csrf);
        return $csrf;
    }
}
