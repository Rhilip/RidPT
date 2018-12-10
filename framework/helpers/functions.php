<?php

/**
 * 助手函数
 * @author 刘健 <coder.liu@qq.com>
 */

if (!function_exists('app')) {
    // 返回当前 App 实例
    function app($prefix = null)
    {
        return \Mix::app($prefix);
    }
}

if (!function_exists('env')) {
    // 获取一个环境变量的值
    function env($name = null)
    {
        return \mix\base\Env::get($name);
    }
}

if (!function_exists('tgo')) {
    // 创建一个带异常捕获的协程
    function tgo($closure)
    {
        go(function () use ($closure) {
            $hook = new \mix\base\ChannelHook();
            try {
                $closure($hook);
            } catch (\Throwable $e) {
                // 钩子处理
                if (!$hook->handle($e)) {
                    // 输出错误
                    \Mix::app()->error->handleException($e);
                }
            }
        });
    }
}
