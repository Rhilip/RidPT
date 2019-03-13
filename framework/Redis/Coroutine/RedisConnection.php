<?php

namespace Rid\Redis\Coroutine;

use Rid\Helpers\CoroutineHelper;

/**
 * RedisCoroutine组件
 */
class RedisConnection extends \Rid\Redis\Persistent\RedisConnection
{

    /**
     * 连接池
     * @var \Rid\Pool\ConnectionPool
     */
    public $connectionPool;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        CoroutineHelper::enableCoroutine();   // 开启协程
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

    // 连接
    protected function connect()
    {
        if (isset($this->connectionPool)) {
            $this->_redis = $this->connectionPool->getConnection(function () {
                return parent::createConnection();
            });
        } else {
            $this->_redis = parent::createConnection();
        }
    }

    // 关闭连接
    public function disconnect()
    {
        if (isset($this->connectionPool) && isset($this->_redis)) {
            $this->connectionPool->releaseConnection($this->_redis, function () {
                parent::disconnect();
            });
        } else {
            parent::disconnect();
        }
    }

    // 重新连接
    protected function reconnect()
    {
        if (isset($this->connectionPool)) {
            $this->connectionPool->destroyConnection(function () {
                parent::disconnect();
            });
        } else {
            parent::disconnect();
        }
        $this->connect();
    }

}
