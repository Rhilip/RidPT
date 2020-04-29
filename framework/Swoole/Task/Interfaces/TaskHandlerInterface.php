<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Task\Interfaces;

use Swoole\Server;

interface TaskHandlerInterface
{
    /**
     * 任务处理方法，返回的值会通过 finish 事件推送给 worker 进程
     * @param array $param
     * @param Server $server
     * @param integer $taskId
     * @param integer $workerId
     * @return mixed
     */
    public function handle(array $param, Server $server, int $taskId, int $workerId);

    /**
     * 任务结束时触发
     * @param Server $server
     * @param int $taskId
     * @param mixed $data
     * @return void
     */
    public function finish(Server $server, int $taskId, $data);
}
