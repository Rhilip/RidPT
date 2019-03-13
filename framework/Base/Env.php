<?php

namespace Rid\Base;

/**
 * 环境类
 */
class Env
{

    // ENV 参数
    protected static $_env = [];

    // 加载环境配置
    public static function load($envFile)
    {
        if (!is_file($envFile)) {
            throw new \Rid\Exceptions\EnvException('Environment file does not exist.');
        }
        $env        = parse_ini_file($envFile);
        self::$_env = array_merge($env, $_SERVER, $_ENV);
    }

    // 获取配置
    public static function get($name = null, $default = '')
    {
        if (is_null($name)) {
            return self::$_env;
        }

        $current = self::$_env;
        if (!isset($current[$name])) {
            return $default;
        }
        $current = $current[$name];
        return $current;
    }

}
