<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Commands;

use Rid\Helpers\IoHelper;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    protected ?SymfonyStyle $io = null;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        IoHelper::setIo($this->io);
        $this->printLogo();
    }

    protected function printLogo()
    {
        $this->io->block(<<<'LOGO'
  ____            __  ____    ______
 /\  _`\   __    /\ \/\  _`\ /\__  _\
 \ \ \L\ \/\_\   \_\ \ \ \L\ \/_/\ \/
  \ \ ,  /\/\ \  /'_` \ \ ,__/  \ \ \
   \ \ \\ \\ \ \/\ \L\ \ \ \/    \ \ \
    \ \_\ \_\ \_\ \___,_\ \_\     \ \_\
     \/_/\/ /\/_/\/__,_ /\/_/      \/_/
LOGO);
    }
}
