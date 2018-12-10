<?php

namespace mix\base;

/**
 * 对象基类
 * @author 刘健 <coder.liu@qq.com>
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
        $config = \Mix::configure($config);
        // 导入属性
        \Mix::importAttributes($this, $config);
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
