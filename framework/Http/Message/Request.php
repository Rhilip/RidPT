<?php

namespace Rid\Http\Message;

use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

/**
 * Request组件
 */
class Request extends HttpFoundationRequest
{
    protected \Swoole\Http\Request $_swoole_request;

    // 设置请求对象
    public function setRequester(\Swoole\Http\Request $request)
    {
        $this->_swoole_request = $request;

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
    }

    public function getSwooleRequest(): \Swoole\Http\Request
    {
        return $this->_swoole_request;
    }
}
