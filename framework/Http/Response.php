<?php

namespace Rid\Http;

use Rid\Base;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

/**
 * Response组件
 */
class Response extends HttpFoundationResponse implements Base\StaticInstanceInterface, Base\ComponentInterface
{
    use Base\StaticInstanceTrait, Base\ComponentTrait;

    /** @var \Swoole\Http\Response */
    protected $_responder;

    /**
     * @var File
     */
    protected $file;
    protected $filename;

    // 是否已经发送
    protected $_isSent = false;

    protected static $trustXSendfileTypeHeader = false;

    public function __construct(array $config = [])
    {
        parent::__construct();
    }

    // 设置响应者
    public function setResponder(\Swoole\Http\Response $responder)
    {
        // 设置响应者
        $this->_responder = $responder;
        $this->_isSent = false;
        $this->cleanResponse();
    }

    private function cleanResponse() {
        $this->headers->replace();
        $this->setContent('');
        $this->setStatusCode(200);
        $this->setProtocolVersion('1.0');
    }

    public function getResponderStatus()
    {
        return isset($this->_responder);
    }

    public function prepare(Request $request)
    {
        if (!is_null($this->file)) {
            $this->headers->set('Content-Type', $this->file->getMimeType() ?: 'application/octet-stream');
        }

        return parent::prepare($request);
    }

    public function send()
    {
        // 多次发送处理
        if ($this->_isSent) {
            return;
        }
        $this->_isSent = true;

        // 清扫组件容器
        \Rid::app()->cleanComponents();

        // 设置Header和Cookies
        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            /** @var array $values */
            foreach ($values as $value) {
                $this->_responder->header($name, (string)$value);
            }
        }

        foreach ($this->headers->getCookies() as $cookie) {
            $this->_responder->cookie(
                $cookie->getName(),
                $cookie->getValue() ?? '',
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain() ?? '',
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->getSameSite() ?? ''
            );
        }

        $this->_responder->status($this->getStatusCode());


        if (!is_null($this->file)) {
            $this->_responder->sendFile($this->file->getRealPath());
        } else {
            $this->_responder->end($this->getContent());
        }
    }

    // 重定向
    public function setRedirect($url, $code = 302)
    {
        $this->headers->set('Location', $url);
        $this->setStatusCode($code);
    }

    /**
     * From \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @param array $data
     * @param string|null $callback
     * @param int $encodingOption
     * @return Response
     */
    public function setJson(array $data = [], string $callback = null, int $encodingOption = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    {
        if (null !== $callback) {
            // partially taken from https://geekality.net/2011/08/03/valid-javascript-identifier/
            // partially taken from https://github.com/willdurand/JsonpCallbackValidator
            //      JsonpCallbackValidator is released under the MIT License. See https://github.com/willdurand/JsonpCallbackValidator/blob/v1.1.0/LICENSE for details.
            //      (c) William Durand <william.durand1@gmail.com>
            $pattern = '/^[$_\p{L}][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}]*(?:\[(?:"(?:\\\.|[^"\\\])*"|\'(?:\\\.|[^\'\\\])*\'|\d+)\])*?$/u';
            $reserved = [
                'break', 'do', 'instanceof', 'typeof', 'case', 'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 'for', 'switch', 'while',
                'debugger', 'function', 'this', 'with', 'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 'extends', 'super',  'const', 'export',
                'import', 'implements', 'let', 'private', 'public', 'yield', 'interface', 'package', 'protected', 'static', 'null', 'true', 'false',
            ];
            $parts = explode('.', $callback);
            foreach ($parts as $part) {
                if (!preg_match($pattern, $part) || \in_array($part, $reserved, true)) {
                    throw new \InvalidArgumentException('The callback name is not valid.');
                }
            }
        }

        $data = json_encode($data, $encodingOption);

        if (null !== $callback) {
            // Not using application/javascript for compatibility reasons with older browsers.
            $this->headers->set('Content-Type', 'text/javascript');

            return $this->setContent(sprintf('/**/%s(%s);', $callback, $data));
        }

        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!$this->headers->has('Content-Type') || 'text/javascript' === $this->headers->get('Content-Type')) {
            $this->headers->set('Content-Type', 'application/json');
        }

        return $this->setContent($data);
    }

    /**
     * Sets the file to stream.
     *
     * @param \SplFileInfo|string $file The file to stream
     * @param string|null $contentDisposition
     * @param bool $autoEtag
     * @param bool $autoLastModified
     * @return $this
     */
    public function setFile($file, string $contentDisposition = null, bool $autoEtag = false, bool $autoLastModified = true)
    {
        if (!$file instanceof File) {
            if ($file instanceof \SplFileInfo) {
                $file = new File($file->getPathname());
            } else {
                $file = new File((string) $file);
            }
        }

        if (!$file->isReadable()) {
            throw new FileException('File must be readable.');
        }

        $this->file = $file;

        if ($autoEtag) {
            $this->setEtag(base64_encode(hash_file('sha256', $this->file->getPathname(), true)));
        }

        if ($autoLastModified) {
            $this->setLastModified(\DateTime::createFromFormat('U', $this->file->getMTime()));
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      ResponseHeaderBag::DISPOSITION_INLINE or ResponseHeaderBag::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     *
     * @return $this
     */
    public function setContentDisposition(string $disposition, string $filename = '', string $filenameFallback = '')
    {
        if ('' === $filename) {
            $filename = $this->file->getFilename();
        }

        if ('' === $filenameFallback && (!preg_match('/^[\x20-\x7e]*$/', $filename) || false !== strpos($filename, '%'))) {
            $encoding = mb_detect_encoding($filename, null, true) ?: '8bit';

            for ($i = 0, $filenameLength = mb_strlen($filename, $encoding); $i < $filenameLength; ++$i) {
                $char = mb_substr($filename, $i, 1, $encoding);

                if ('%' === $char || \ord($char) < 32 || \ord($char) > 126) {
                    $filenameFallback .= '_';
                } else {
                    $filenameFallback .= $char;
                }
            }
        }

        $dispositionHeader = $this->headers->makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers->set('Content-Disposition', $dispositionHeader);

        return $this;
    }
}
