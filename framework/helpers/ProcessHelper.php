<?php

namespace mix\helpers;

/**
 * ProcessHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class ProcessHelper
{

    // 使当前进程蜕变为一个守护进程
    public static function daemon($closeStandardInputOutput = true)
    {
        return \Swoole\Process::daemon(true, !$closeStandardInputOutput);
    }

    // 设置进程标题
    public static function setTitle($title)
    {
        if (PhpInfoHelper::isMac()) {
            return false;
        }
        if (!function_exists('cli_set_process_title')) {
            return false;
        }
        return @cli_set_process_title($title);
    }

    // 检查 PID 是否运行
    public static function isRunning($pid)
    {
        return self::kill($pid, 0);
    }

    // kill 进程
    public static function kill($pid, $signal = null)
    {
        if (is_null($signal)) {
            return \Swoole\Process::kill($pid);
        }
        return \Swoole\Process::kill($pid, $signal);
    }

    // 返回当前进程ID
    public static function getPid()
    {
        return getmypid();
    }

    // 写入 PID 文件
    public static function writePidFile($pidFile)
    {
        $pid = ProcessHelper::getPid();
        $ret = file_put_contents($pidFile, $pid, LOCK_EX);
        return $ret ? true : false;
    }

    // 读取 PID 文件
    public static function readPidFile($pidFile)
    {
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = file_get_contents($pidFile);
        if (self::isRunning($pid)) {
            return $pid;
        }
        return false;
    }

}
