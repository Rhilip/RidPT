<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Commands\Server;

use Rid\Helpers\ProcessHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends AbstractServerStartCommand
{
    protected static $defaultName = 'server:stop';

    protected function configure(): void
    {
        $this->setDescription('Stop Swoole HTTP server');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
    }

    protected function stopServer()
    {
        if ($pid = $this->getPid()) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                usleep(100000);  // 等待进程退出
            }
            println('rid-httpd stop completed.');
        } else {
            println('rid-httpd is not running.');
        }
        return 0; // 返回退出码
    }
}
