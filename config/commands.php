<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 9/17/2019
 * Time: 2019
 */

return [
    'server:start' => \App\Commands\Server\StartCommand::class,
    'server:stop' => \App\Commands\Server\StopCommand::class,
    'server:restart' => \App\Commands\Server\RestartCommand::class,
    'server:reload' => \App\Commands\Server\ReloadCommand::class,
    'server:status' => \App\Commands\Server\StatusCommand::class
];
