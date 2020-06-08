<?php

namespace Rid\DBAL;

/**
 * Persistent PDO Connection
 */
class Connection extends AbstractConnection
{

    // 重新连接
    protected function reconnect()
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * 返回结果集
     * @return \PDOStatement
     */
    public function fetch()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一行
    public function fetchOne()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回多行
    public function fetchAll()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一列 (第一列)
    public function fetchColumn($columnNumber = 0)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一个标量值
    public function fetchScalar()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 执行SQL语句
    public function execute()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 开始事务
    public function beginTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * 执行方法
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Throwable
     */
    public function call($name, $arguments)
    {
        try {
            // 执行父类方法
            return parent::$name(...$arguments);
        } catch (\Throwable $e) {
            if (self::isDisconnectException($e)) {
                // 断开连接异常处理
                $this->reconnect();
                // 重新执行方法
                return $this->call($name, $arguments);
            } else {
                // 抛出其他异常
                throw $e;
            }
        }
    }

    // 判断是否为断开连接异常
    protected static function isDisconnectException(\Throwable $e)
    {
        $disconnectMessages = [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
            'failed with errno',
        ];
        $errorMessage = $e->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }
}
