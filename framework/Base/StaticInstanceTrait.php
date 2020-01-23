<?php

namespace Rid\Base;

/**
 * Trait StaticInstanceTrait
 */
trait StaticInstanceTrait
{

    /**
     * 使用静态方法创建实例
     * @param mixed ...$args
     * @return $this
     */
    public static function new(...$args)
    {
        return new static(...$args);
    }

    /**
     * 创建实例，通过配置名
     * @param $name
     * @return $this
     */
    public static function newInstanceByConfig($name)
    {
        $class  = get_called_class();
        $config = \Rid::app()->env($name);
        $object = \Rid::createObject($config);
        if (get_class($object) != $class) {
            throw new \Rid\Exceptions\ConfigException('实例化类型与配置类型不符，期望 :' . $class . '当前:' . get_class($object));
        }
        return $object;
    }

    protected function importAttributes($config)
    {
        foreach ($config as $name => $value) {
            $this->$name = $value;
        }
    }
}
