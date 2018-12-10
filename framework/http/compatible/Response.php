<?php

namespace mix\http\compatible;

/**
 * Response组件
 * @author 刘健 <coder.liu@qq.com>
 */
class Response extends \mix\http\BaseResponse
{

    // 请求前置事件
    public function onRequestBefore()
    {
        parent::onRequestBefore();
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
        return setcookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
    }

    // 重定向
    public function redirect($url)
    {
        $this->setHeader('Location', $url);
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
        \Mix::app()->cleanComponents();
        // 发送
        $this->sendStatusCode();
        $this->sendHeaders();
        $this->sendContent();
    }

    // 发送HTTP状态码
    protected function sendStatusCode()
    {
        header("HTTP/1.1 {$this->statusCode}");
    }

    // 发送Header信息
    protected function sendHeaders()
    {
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
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
        echo $this->content;
    }

}
