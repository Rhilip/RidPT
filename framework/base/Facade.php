<?php

namespace mix\base;

/**
 * 门面基类
 * @author 刘健 <coder.liu@qq.com>
 */
class Facade
{

    // 执行静态方法
    public static function __callStatic($name, $arguments)
    {
        if (method_exists(get_called_class(), 'getInstance')) {
            $instance = static::getInstance();
        } else {
            $instance = static::getInstances();
        }
        if (is_array($instance)) {
            $instance = array_shift($instance);
        }
        return call_user_func_array([$instance, $name], $arguments);
    }

}
