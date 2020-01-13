<?php

namespace Rid\Redis\Persistent;

/**
 * redis长连接组件
 */
class RedisConnection extends BaseRedisConnection
{

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }
}
