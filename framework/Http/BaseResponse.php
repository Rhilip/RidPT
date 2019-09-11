<?php

namespace Rid\Http;

use Rid\Base\Component;

/**
 * Response组件基类
 */
class BaseResponse extends Component
{

    // 格式值
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';
    const FORMAT_JSONP = 'jsonp';
    const FORMAT_RAW = 'raw';

    // 默认输出格式
    public $defaultFormat = self::FORMAT_HTML;

    // 当前输出格式
    public $format;

    // 状态码
    public $statusCode = 200;

    // 内容
    public $content = '';

    // HTTP 响应头
    public $headers = [];

    // 是否已经发送
    protected $_isSent = false;

    // 设置Header信息
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setStatusCode($code)
    {
        $this->statusCode = $code;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    // 预处理
    protected function prepare()
    {
        // 设置默认 Content-Type 信息
        $headers = array_change_key_case($this->headers, CASE_LOWER);
        if (!isset($headers['content-type'])) {
            switch ($this->format) {
                case self::FORMAT_HTML:
                    $this->setHeader('Content-Type', 'text/html; charset=utf-8');
                    break;
                case self::FORMAT_JSON:
                    $this->setHeader('Content-Type', 'application/json; charset=utf-8');
                    break;
                case self::FORMAT_JSONP:
                    $this->setHeader('Content-Type', 'application/json; charset=utf-8');
                    break;
            }
        }
        // 转换内容为字符型
        $content = $this->content;
        is_null($content) and $content = '';
        if (is_array($content) || is_object($content)) {
            switch ($this->format) {
                case self::FORMAT_JSON:
                    $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    break;
                case self::FORMAT_JSONP:
                    {
                        $callback_key = app()->request->get('callback', 'callback');
                        $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        $content = $callback_key . '(' . $content . ')';
                        break;
                    }
            }
        }
        $this->content = $content;
    }

}
