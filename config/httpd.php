<?php

// Console应用配置
return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 命令命名空间
    'commandNamespace' => 'App\Commands',

    // 命令
    'commands'         => [

        'service start'   => ['Service', 'Start', 'description' => 'Start the rid-httpd service.'],
        'service stop'    => ['Service', 'Stop', 'description' => 'Stop the rid-httpd service.'],
        'service restart' => ['Service', 'Restart', 'description' => 'Restart the rid-httpd service.'],
        'service reload'  => ['Service', 'Reload', 'description' => 'Reload the worker process of the rid-httpd service.'],
        'service taskreload' => ['Service','TaskReload' , 'description' => 'Reload the task process of the rid-httpd service.'],
        'service status'  => ['Service', 'Status', 'description' => 'Check the status of the rid-httpd service.'],

    ],

    // 组件配置
    'components'       => [

        'input'  => [
            'class' => Rid\Console\Input::class,
        ],

        'error'  => [
            'class' => Rid\Console\Error::class,
            'level' => E_ALL,
        ],

        'log'    => [
            'class'       => Rid\Component\Log::class,
            'logDir'      => 'logs',
            'logRotate'   => Rid\Component\Log::ROTATE_DAY,
            'maxFileSize' => 1024,   // bytes
        ],

    ],

    // 用户自定义进程 （用于常驻的任务清理，将会使用Server->addProcess添加到Server
    'process' => [
        'tracker' => [
            'class' => App\Process\TrackerAnnounceProcess::class,
            'title' => 'Tracker Announce Worker',
            'components' => ['log', 'pdo', 'redis', 'config'],
            'sleep' => 5,
        ],
        'crontab' => [
            'class' => App\Process\CronTabProcess::class,
            'title' => 'Crontab Worker',
            'components' => ['log', 'pdo', 'redis', 'config'],
            'sleep' => 60,
        ]
    ],

    // 类库配置
    'libraries'        => [

        // HttpServer
        'httpServer' => [

            'class'       => Rid\Http\HttpServer::class,  // 类路径

            // 虚拟主机：运行在服务器内的 HTTP 服务
            'virtualHost' => [
                'host'       => '127.0.0.1',
                'port'       => 9501,
                'configFile' => __DIR__ . '/http_base.php',
            ],

            // 运行参数：https://wiki.swoole.com/wiki/page/274.html
            'settings'    => [
                'enable_coroutine' => false,  // 开启协程
                'reactor_num'      => 1,  // 连接处理线程数
                'worker_num'       => 5,  // 工作进程数
                'pid_file'         => '/var/run/rid-httpd.pid',  // PID 文件
                'log_file'         => '/tmp/rid-httpd.log',  // 日志文件路径
                'max_request'      => 3000, // 进程的最大任务数
                'max_wait_time'    => 60, // 退出等待时间
                'package_max_length' => 6242880, // 最大上传包大小，单位 Bytes
                'buffer_output_size' => 33554432,  // 发送缓存区大小，影响向用户发送文件的最大大小，单位 Bytes
                'reload_async'     => true, // 异步安全重启
                /* 'user'        => 'www',  // 子进程运行用户  */
            ],

        ],

    ],

];
