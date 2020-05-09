<?php

/**
 * Rid类
 */
class Rid
{

    // 版本号
    const VERSION = 'v0.1.6-alpha';

    // App实例
    protected static $_app;

    /**
     * 返回App，并设置组件命名空间
     *
     * @param null $prefix
     * @return \Rid\Http\Application
     */
    public static function app($prefix = null)
    {
        // 返回App
        return self::getApp();
    }

    /**
     * 获取App
     *
     * @return \Rid\Http\Application
     */
    protected static function getApp()
    {
        return self::$_app;
    }

    // 设置App
    public static function setApp($app)
    {
        self::$_app = $app;
    }
}
