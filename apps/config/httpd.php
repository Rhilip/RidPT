<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'apps\commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service', 'Start', 'description' => 'Start the mix-httpd service.'],
        'service stop'    => ['Service', 'Stop', 'description' => 'Stop the mix-httpd service.'],
        'service restart' => ['Service', 'Restart', 'description' => 'Restart the mix-httpd service.'],
        'service reload'  => ['Service', 'Reload', 'description' => 'Reload the worker process of the mix-httpd service.'],
        'service status'  => ['Service', 'Status', 'description' => 'Check the status of the mix-httpd service.'],

    ],

    // 组件配置
    'components'       => [

        'input'  => [
            'class' => Mix\Console\Input::class,
        ],

        'output' => [
            'class' => Mix\Console\Output::class,
        ],

        'error'  => [
            'class' => Mix\Console\Error::class,
            'level' => E_ALL,
        ],

        'log'    => [
            'class'       => Mix\Log\Log::class,
            'level'       => ['error', 'info', 'debug'],
            'logDir'      => 'logs',
            'logRotate'   => Mix\Log\Log::ROTATE_DAY,
            'maxFileSize' => 1024,   // bytes
        ],

    ],

    // 类库配置
    'libraries'        => [

        // HttpServer
        'httpServer' => [

            // 类路径
            'class'       => Mix\Http\HttpServer::class,

            // 虚拟主机：运行在服务器内的 HTTP 服务
            'virtualHost' => [
                'host'       => '127.0.0.1',
                'port'       => 9501,
                'configFile' => __DIR__ . '/http_permanent.php',
            ],

            // 运行参数：https://wiki.swoole.com/wiki/page/274.html
            'settings'    => [
                // 开启协程
                'enable_coroutine' => false,
                // 连接处理线程数
                'reactor_num'      => 8,
                // 工作进程数
                'worker_num'       => 8,
                // PID 文件
                'pid_file'         => '/var/run/mix-httpd.pid',
                // 日志文件路径
                'log_file'         => '/tmp/mix-httpd.log',
                // 进程的最大任务数
                'max_request'      => 10000,
                // 退出等待时间
                'max_wait_time'    => 60,
                // 异步安全重启
                'reload_async'     => true,
                // 子进程运行用户
                /* 'user'        => 'www', */
            ],

        ],

    ],

];
