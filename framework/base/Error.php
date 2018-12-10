<?php

namespace mix\base;

/**
 * Error类
 * @author 刘健 <coder.liu@qq.com>
 */
class Error
{

    // 已经注册
    protected static $registered = false;

    // 注册错误处理
    public static function register()
    {
        // 多次注册处理
        if (self::$registered) {
            return;
        }
        self::$registered = true;
        // 注册错误处理
        $level = \Mix::app()->error->level;
        error_reporting($level);
        set_error_handler([__CLASS__, 'appError']);
        set_exception_handler([__CLASS__, 'appException']); // swoole 不支持该函数
        register_shutdown_function([__CLASS__, 'appShutdown']);
    }

    // 错误处理
    public static function appError($errno, $errstr, $errfile = '', $errline = 0)
    {
        throw new \mix\exceptions\ErrorException($errno, $errstr, $errfile, $errline);
    }

    // 停止处理
    public static function appShutdown()
    {
        if ($error = error_get_last()) {
            self::appException(new \mix\exceptions\ErrorException($error['type'], $error['message'], $error['file'], $error['line']));
        }
    }

    // 异常处理
    public static function appException($e)
    {
        \Mix::app()->error->handleException($e, true);
    }

}
