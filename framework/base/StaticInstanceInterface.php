<?php

namespace mix\base;

/**
 * Interface InstanceInterface
 * @author 刘健 <coder.liu@qq.com>
 */
interface StaticInstanceInterface
{

    /**
     * 创建实例，通过配置名
     * @param $name
     * @return $this
     */
    public static function newInstanceByConfig($name);

}
