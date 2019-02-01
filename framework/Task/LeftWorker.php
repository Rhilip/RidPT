<?php

namespace Rid\Task;

/**
 * 工作者基类(左)
 */
class LeftWorker extends BaseWorker
{

    // 发送消息到右进程
    public function send($data)
    {
        return $this->inputQueue->push($data);
    }

}
