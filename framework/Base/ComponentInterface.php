<?php

namespace Rid\Base;

/**
 * Interface ComponentInterface
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

    // 请求前置事件
    public function onRequestBefore();

    // 请求后置事件
    public function onRequestAfter();
}
