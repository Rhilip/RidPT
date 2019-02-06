<?php

/**
 * 助手函数
 */

if (!function_exists('app')) {
    /** 返回当前 App 实例
     * @param null $prefix
     * @return \Rid\Console\Application|\Rid\Http\Application
     */
    function app($prefix = null)
    {
        return \Rid::app($prefix);
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
        return \Rid\Config\Env::get($name, $default);
    }
}

if (!function_exists('tgo')) {

    /** 创建一个带异常捕获的协程
     * @param $closure
     */
    function tgo($closure)
    {
        go(function () use ($closure) {
            $hook = new \Rid\Base\ChannelHook();
            try {
                $closure($hook);
            } catch (\Throwable $e) {
                // 钩子处理
                if (!$hook->handle($e)) {
                    // 输出错误
                    \Rid::app()->error->handleException($e);
                }
            }
        });
    }
}

if (!function_exists('println')) {
    // 输出字符串并换行
    function println($expression)
    {
        echo $expression . PHP_EOL;
    }
}
