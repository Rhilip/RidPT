<?php

namespace mix\base;

/**
 * 组件基类Interface
 * @author 刘健 <coder.liu@qq.com>
 */
interface ComponentInterface
{

    // 协程模式值
    const COROUTINE_MODE_NEW = 0;
    const COROUTINE_MODE_REFERENCE = 1;

    // 状态值
    const STATUS_READY = 0;
    const STATUS_RUNNING = 1;

    // 获取状态
    public function getStatus();

    // 设置状态
    public function setStatus($status);

    // 获取协程模式
    public function getCoroutineMode();

    // 设置协程模式
    public function setCoroutineMode($coroutineMode);

    // 请求前置事件
    public function onRequestBefore();

    // 请求后置事件
    public function onRequestAfter();

}
