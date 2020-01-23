<?php

namespace Rid\Base;

/**
 * 对象基类
 */
abstract class BaseObject implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    // 构造
    public function __construct($config = [])
    {
        // 执行构造事件
        $this->onConstruct();
        // 构建配置
        $config = \Rid::configure($config);
        // 导入属性
        $this->importAttributes($config);
        // 执行初始化事件
        $this->onInitialize();
    }

    // 析构
    public function __destruct()
    {
        $this->onDestruct();
    }

    // 构造事件
    public function onConstruct()
    {
    }

    // 初始化事件
    public function onInitialize()
    {
    }

    // 析构事件
    public function onDestruct()
    {
    }
}
