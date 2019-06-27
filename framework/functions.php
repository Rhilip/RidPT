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
        return \Rid\Base\Env::get($name, $default);
    }
}

if (!function_exists('__')) {
    function __($string, $avg = null, $lang = null)
    {
        return app()->i18n->trans($string, $avg, $lang);
    }
}

if (!function_exists('config')) {
    function config($config, $throw = true)
    {
        return app()->config->get($config, $throw);
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

if (!function_exists('is_indexed_array')) {
    /** 索引数组：所有键名都为数值型，注意字符串类型的数字键名会被转换为数值型。
     * 判断数组是否为索引数组
     * @param array $arr
     * @return bool
     */
    function is_indexed_array(array $arr): bool
    {
        if (is_array($arr)) {
            return count(array_filter(array_keys($arr), 'is_string')) === 0;
        }
        return false;
    }
}

if (!function_exists('is_continuous_indexed_array')) {
    /** 连续索引数组：键名是连续性的数字。
     * 判断数组是否为连续的索引数组
     * 以下这种索引数组为非连续索引数组
     * [
     *   0 => 'a',
     *   2 => 'b',
     *   3 => 'c',
     *   5 => 'd',
     * ]
     * @param array $arr
     * @return bool
     */
    function is_continuous_indexed_array(array $arr): bool
    {
        if (is_array($arr)) {
            $keys = array_keys($arr);
            return $keys == array_keys($keys);
        }
        return false;
    }
}

if (!function_exists('is_assoc_array')) {
    /** 关联数组：所有键名都为字符串型，注意字符串类型的数字键名会被转换为数值型。
     * 判断数组是否为关联数组
     * @param array $arr
     * @return bool
     */
    function is_assoc_array(array $arr): bool
    {
        if (is_array($arr)) {
            // return !is_indexed_array($arr);
            return count(array_filter(array_keys($arr), 'is_string')) === count($arr);
        }
        return false;
    }
}

if (!function_exists('is_assoc_array')) {
    /** 混合数组：键名既有数值型也有字符串型。
     * 判断数组是否为混合数组
     * @param array $arr
     * @return bool
     */
    function is_mixed_array(array $arr): bool
    {
        if (is_array($arr)) {
            $count = count(array_filter(array_keys($arr), 'is_string'));
            return $count !== 0 && $count !== count($arr);
        }
        return false;
    }
}
