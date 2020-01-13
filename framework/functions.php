<?php

/**
 * 助手函数
 */

if (!function_exists('app')) {
    /** 返回当前 App 实例
     * @param null $prefix
     * @return \Rid\Http\Application
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
        if ($name === null) {
            return $_ENV;
        }
        return $_ENV[$name] ?? $default;
    }
}

if (!function_exists('__')) {
    function __(string $string, array $avg = [], $domain = null, $lang = null)
    {
        return app()->i18n->trans($string, $avg, $domain, $lang);
    }
}

if (!function_exists('config')) {
    function config(string $config)
    {
        return app()->config->get($config);
    }
}

if (!function_exists('println')) {
    // 输出字符串并换行
    function println($expression)
    {
        echo date('Y-m-d H:i:s') . ' ' . $expression . PHP_EOL;
    }
}

if (!function_exists('array_set_default')) {
    function array_set_default(&$array, $defaults)
    {
        if (!is_array($array)) {
            $array = [$array];
        }
        foreach ($defaults as $key => $default) {
            if (!array_key_exists($key, $array)) {
                $array[$key] = $default;
            }
        }
    }
}

if (!function_exists('input2array')) {
    function input2array($input)
    {
        return is_array($input) ? $input : [$input];
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

if (!function_exists('is_mixed_array')) {
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
