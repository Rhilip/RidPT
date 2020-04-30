<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Task;

use Rid\Swoole\Helper\ServerHelper;

class TaskManager
{
    /**
     * 投递异步任务
     * 调用成功返回任务ID，失败返回false
     * @param TaskInfo $taskInfo
     * @param int $workerID
     * @return false|int
     */
    public static function post(TaskInfo $taskInfo, int $workerID = -1)
    {
        return ServerHelper::getServer()->task($taskInfo, $workerID, [$taskInfo->getHandler(), 'finish']);
    }
}
