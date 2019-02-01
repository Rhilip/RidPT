<?php

namespace Rid\Task;

/**
 * 工作者基类(中)
 */
class CenterWorker extends BaseWorker
{

    // 发送消息到右进程
    public function send($data)
    {
        return $this->outputQueue->push($data);
    }
    
}
