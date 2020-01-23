<?php

namespace Rid\Http;

use Rid\Base;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

/**
 * Request组件
 */
class Request extends HttpFoundationRequest implements Base\StaticInstanceInterface, Base\ComponentInterface
{
    use Base\StaticInstanceTrait, Base\ComponentTrait;

    protected $_swoole_request;
    protected $_route = [];

    /**
     * Uploaded files from Swoole.
     *
     * @var array
     */
    public $raw_files;

    public $start_at;

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct($config = [])
    {
        $this->setTrustedField($config);
    }

    private function setTrustedField($config = [])
    {
        if (\array_key_exists('trustedHosts', $config)) {
            self::setTrustedHosts($config['trustedHosts']);
        }
        if (\array_key_exists('trustedProxies', $config)) {
            self::setTrustedProxies($config['trustedProxies'], $config['trustedHeaderSet'] ?? Request::HEADER_X_FORWARDED_ALL);
        }
    }

    // 设置请求对象
    public function setRequester(\Swoole\Http\Request $request)
    {
        $this->_swoole_request = $request;
        $this->start_at = microtime(true);

        $server = \array_change_key_case($request->server, CASE_UPPER);

        // Add formatted headers to server
        foreach ($request->header as $key => $value) {
            $server['HTTP_' . \mb_strtoupper(\str_replace('-', '_', $key))] = $value;
        }

        $this->initialize(
            $request->get ?? [],
            $request->post ?? [],
            [],
            $request->cookie ?? [],
            $request->files ?? [],
            $server,
            $request->rawContent()
        );
        $this->raw_files = $request->files;
    }

    // 设置 ROUTE 值
    public function setRoute($route)
    {
        $this->_route = $route;
    }

    public function route($name = null, $default = null)
    {
        return is_null($name) ? $this->_route : ($this->_route[$name] ?? $default);
    }

    /**
     * @return mixed
     */
    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->_swoole_request;
    }
}
