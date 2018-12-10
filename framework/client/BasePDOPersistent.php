<?php

namespace mix\client;

/**
 * BasePdoPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 */
class BasePDOPersistent extends BasePDO
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
    public function query()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一行
    public function queryOne()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回多行
    public function queryAll()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一列 (第一列)
    public function queryColumn($columnNumber = 0)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    // 返回一个标量值
    public function queryScalar()
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

    // 执行方法
    public function call($name, $arguments)
    {
        try {
            // 执行父类方法
            return call_user_func_array("parent::{$name}", $arguments);
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
        $errorMessage       = $e->getMessage();
        foreach ($disconnectMessages as $message) {
            if (false !== stripos($errorMessage, $message)) {
                return true;
            }
        }
        return false;
    }

}
