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
use Rid\Http\HttpServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractServerStartCommand extends Command
{
    protected ?array $httpServerConfig = null;

    protected function configure(): void
    {
        $this->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Run Server in daemon mode.')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Run Server in hot mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepareServerConfig($input);
    }

    protected function prepareServerConfig(InputInterface $input)
    {
        $this->httpServerConfig = require RIDPT_ROOT . '/config/httpServer.php';
        if ($input->getOption('update')) {
            $this->httpServerConfig['settings']['max_request'] = 1;
        }
        $this->httpServerConfig['settings']['daemonize'] = (int)$input->getOption('daemon');
    }

    protected function startServer()
    {
        if ($pid = $this->getPid()) {
            println("rid-httpd is running, PID : {$pid}.");
            return 1;
        }

        $server = new HttpServer($this->httpServerConfig);
        $server->start();
        return 0;  // 返回退出码
    }

    protected function getPidFile()
    {
        return $this->httpServerConfig['settings']['pid_file'];
    }

    protected function getPid()
    {
        return ProcessHelper::readPidFile($this->getPidFile());
    }
}
