<?php

namespace Rid\Base;

/**
 * Interface StaticInstanceInterface
 */
interface StaticInstanceInterface
{

    /**
     * 使用静态方法创建实例
     * @param mixed ...$args
     * @return $this
     */
    public static function new(...$args);

    /**
     * 创建实例，通过配置名
     * @param $name
     * @return $this
     */
    public static function newInstanceByConfig($name);
}
