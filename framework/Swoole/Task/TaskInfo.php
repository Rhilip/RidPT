<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Task;

use Rid\Swoole\Task\Interfaces\TaskHandlerInterface;

class TaskInfo
{
    private string $handler;
    private array $params;

    public function __construct(string $handler = '', array $params = [])
    {
        $this->handler = $handler;
        $this->params = $params;
    }

    /**
     * @return TaskHandlerInterface
     */
    public function getHandler(): TaskHandlerInterface
    {
        return new $this->handler;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
