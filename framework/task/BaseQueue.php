<?php

namespace mix\task;

use mix\base\BaseObject;

/**
 * 消息队列基类
 * @author 刘健 <coder.liu@qq.com>
 */
class BaseQueue extends BaseObject
{

    // 队列对象
    public $queue;

    // 临时文件目录
    public $tempDir;

    // 进程对象
    public $worker;

    // 共享内存表
    public $table;

    // 投递数据
    public function push($data)
    {
        $data = serialize($data);
        if (strlen($data) > 8000) {
            $data = serialize(new TempMessage($data, $this->tempDir));
        }
        return $this->queue->push($data);
    }

    // 提取数据
    public function pop()
    {
        $data = $this->queue->pop();
        if (!empty($data)) {
            $data = unserialize($data);
            if ($data instanceof TempMessage) {
                $data = unserialize($data->getContent());
            }
        }
        return $data;
    }

    // 队列是否为空
    public function isEmpty()
    {
        return $this->queue->statQueue()['queue_num'] == 0;
    }

    // 是否在左进程
    protected function isLeftWorker()
    {
        return $this->worker instanceof LeftWorker;
    }

    // 是否在左进程
    protected function isCenterWorker()
    {
        return $this->worker instanceof CenterWorker;
    }

    // 是否在右进程
    protected function isRightWorker()
    {
        return $this->worker instanceof RightWorker;
    }

    // 是否重启
    protected function isRestart()
    {
        return $this->table->get('signal', 'value') == ProcessPoolTaskExecutor::SIGNAL_RESTART;
    }

    // 是否停止左进程
    protected function isStopLeft()
    {
        return $this->table->get('signal', 'value') == ProcessPoolTaskExecutor::SIGNAL_STOP_LEFT;
    }

    // 是否停止全部进程
    protected function isStopAll()
    {
        return $this->table->get('signal', 'value') == ProcessPoolTaskExecutor::SIGNAL_STOP_ALL;
    }

}