<?php

namespace mix\task;

/**
 * 输出消息队列
 * @author 刘健 <coder.liu@qq.com>
 */
class OutputQueue extends BaseQueue
{

    // 提取数据
    public function pop()
    {
        // 重启信号处理
        if ($this->isRightWorker() && ($this->isRestart() || $this->isStopAll())) {
            $this->worker->exit();
        }
        // 提取数据
        return parent::pop();
    }

}