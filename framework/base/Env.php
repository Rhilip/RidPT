<?php

namespace Mix\Base;

/**
 * 环境类
 * @author 刘健 <coder.liu@qq.com>
 */
class Env
{

    // ENV 参数
    protected static $_env = [];

    // 加载环境配置
    public static function load($envFile)
    {
        if (!is_file($envFile)) {
            throw new \Mix\Exceptions\EnvException('Environment file does not exist.');
        }
        $env        = parse_ini_file($envFile, true);
        self::$_env = array_merge($env, $_SERVER, $_ENV);
    }

    // 获取配置
    public static function get($name = null)
    {
        if (is_null($name)) {
            return self::$_env;
        }
        $fragments = explode('.', $name);
        $current   = self::$_env;
        foreach ($fragments as $key) {
            if (!isset($current[$key])) {
                throw new \Mix\Exceptions\EnvException("Environment config does not exist: {$name}.");
            }
            $current = $current[$key];
        }
        return $current;
    }

}
