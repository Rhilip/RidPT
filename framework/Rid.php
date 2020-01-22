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
        // 获取App
        $app = self::getApp();
        // 设置组件命名空间
        $app->setComponentPrefix($prefix);
        // 返回App
        return $app;
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

    // 构建配置
    public static function configure($config, $instantiation = false)
    {
        foreach ($config as $key => $value) {
            // 子类实例化
            if (is_array($value)) {
                // 实例化
                if (isset($value['class'])) {
                    $config[$key] = self::configure($value, true);
                }
                // 引用其他组件
                if (isset($value['component'])) {
                    $componentPrefix = null;
                    $componentName   = $value['component'];
                    if (strpos($value['component'], '.') !== false) {
                        $fragments       = explode('.', $value['component']);
                        $componentName   = array_pop($fragments);
                        $componentPrefix = implode('.', $fragments);
                    }
                    $config[$key] = self::app($componentPrefix)->$componentName;
                }
            }
        }
        if ($instantiation) {
            $class = $config['class'];
            unset($config['class']);
            return new $class($config);
        }
        return $config;
    }

    // 使用配置创建对象
    public static function createObject($config)
    {
        $class = $config['class'];
        unset($config['class']);
        return new $class($config);
    }
}
