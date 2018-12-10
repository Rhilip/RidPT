<?php

namespace mix\websocket;

use mix\base\BaseObject;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends BaseObject
{

    /**
     * 服务
     * @var \Swoole\WebSocket\Server
     */
    public $server;

    // 文件描述符
    public $fd;

}
