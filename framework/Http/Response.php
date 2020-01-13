<?php

namespace Rid\Http;

/**
 * Response组件
 */
class Response extends BaseResponse
{

    /** @var \Swoole\Http\Response */
    protected $_responder;

    // 设置响应者
    public function setResponder($responder)
    {
        // 设置响应者
        $this->_responder = $responder;
        // 重置数据
        $this->format     = $this->defaultFormat;
        $this->statusCode = 200;
        $this->content    = '';
        $this->headers    = [];
        $this->_isSent    = false;
    }

    // 设置Cookie
    public function setCookie($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httpOnly = false)
    {
        return $this->_responder->cookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    // 重定向
    public function redirect($url, $code = 302)
    {
        $this->setHeader('Location', $url);
        $this->statusCode = $code;
    }

    // 发送
    public function send()
    {
        // 多次发送处理
        if ($this->_isSent) {
            return;
        }
        $this->_isSent = true;
        // 预处理
        $this->prepare();
        // 清扫组件容器
        \Rid::app()->cleanComponents();
        // 发送
        $this->sendStatusCode();
        $this->sendHeaders();
        $this->sendContent();
    }

    // 发送 HTTP 状态码
    protected function sendStatusCode()
    {
        $this->_responder->status($this->statusCode);
    }

    // 发送 Header 信息
    protected function sendHeaders()
    {
        foreach ($this->headers as $key => $value) {
            $this->_responder->header($key, $value);
        }
    }

    // 发送内容
    protected function sendContent()
    {
        // 非标量处理
        if (!is_scalar($this->content)) {
            $this->content = ucfirst(gettype($this->content));
        }
        // 发送内容
        $this->_responder->end($this->content);
    }

    public function getResponderStatus()
    {
        return isset($this->_responder);
    }
}
