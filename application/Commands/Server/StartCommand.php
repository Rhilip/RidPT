<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Commands\Server;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends AbstractServerCommand
{
    protected static $defaultName = 'server:start';

    protected function configure(): void
    {
        $this->setDescription('Start to Run Swoole HTTP server');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->prepareServer();
        return $this->startServer();
    }
}
