<?php

/**
 * 助手函数
 * @author 刘健 <coder.liu@qq.com>
 */

if (!function_exists('app')) {
    /** 返回当前 App 实例
     * @param null $prefix
     * @return \Mix\Console\Application|\Mix\Http\Application
     */
    function app($prefix = null)
    {
        return \Mix::app($prefix);
    }
}

if (!function_exists('env')) {
    /** 获取一个环境变量的值
     * @param null $name
     * @param string $default
     * @return array|mixed|string
     */
    function env($name = null, $default = '')
    {
        return \Mix\Config\Env::get($name, $default);
    }
}

if (!function_exists('tgo')) {

    /** 创建一个带异常捕获的协程
     * @param $closure
     */
    function tgo($closure)
    {
        go(function () use ($closure) {
            $hook = new \Mix\Base\ChannelHook();
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
