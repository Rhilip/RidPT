<?php

namespace mix\helpers;

/**
 * CoroutineHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class CoroutineHelper
{

    // 是否为协程环境
    public static function isCoroutine()
    {
        if (!class_exists('\Swoole\Coroutine')) {
            return false;
        }
        if (\Swoole\Coroutine::getuid() == -1) {
            return false;
        }
        return true;
    }

    // 开启协程
    public static function enableCoroutine()
    {
        static $trigger = false;
        if (!$trigger) {
            \Swoole\Runtime::enableCoroutine(true); // Swoole >= 4.1.0
            $trigger = true;
        }
    }

    // 关闭协程
    public static function disableCoroutine()
    {
        static $trigger = false;
        if (!$trigger) {
            swoole_async_set([
                'enable_coroutine' => false,
            ]);
            $trigger = true;
        }
    }

}
