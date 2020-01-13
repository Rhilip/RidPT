<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 9/17/2019
 * Time: 2019
 */

use Swoole\Http\Request;
use Swoole\Http\Server;

return [
    //异步Server对象运行的主机地址
    'host' => '127.0.0.1',

    //异步Server对象监听的端口
    'port' => 9501,

    // 独立进程的配置文件
    'configFile' => __DIR__ . '/application.php',

    // 运行参数：https://wiki.swoole.com/wiki/page/274.html
    'settings' => [
        'enable_coroutine' => false,  // 开启协程
        'reactor_num' => 1,  // 连接处理线程数
        'worker_num' => 5,  // 工作进程数
        'pid_file' => dirname(__DIR__) . '/var/runtime/ridpt.pid',  // PID 文件
        'log_file' => dirname(__DIR__) . '/var/runtime/ridpt.error.log',  // 日志文件路径
        'max_request' => 3000, // 进程的最大任务数
        'max_wait_time' => 60, // 退出等待时间
        'package_max_length' => 6242880, // 最大上传包大小，单位 Bytes
        'buffer_output_size' => 33554432,  // 发送缓存区大小，影响向用户发送文件的最大大小，单位 Bytes
        'reload_async' => true, // 异步安全重启
        /* 'user'        => 'www',  // 子进程运行用户  */
    ],

    // 注册回调方法Hook
    'hook' => [
        // 主进程启动事件（onStart）回调
        'hook_start' => function (Server $server) {
        },

        // 主进程停止事件回调
        'hook_shutdown' => function (Server $server) {
        },

        // 管理进程启动事件回调
        'hook_manager_start' => function (Server $server) {
        },

        // 管理进程停止事件回调
        'hook_manager_stop' => function (Server $server) {
        },

        // 工作进程启动事件回调
        'hook_worker_start' => function (Server $server, int $worker_id) {
        },

        // 工作进程停止事件回调
        'hook_worker_stop' => function (Server $server, int $workerId) {
        },

        // 工作进程错误事件（onWorkerError）回调
        'hook_worker_error' => function (Server $server, int $workerId, int $workerPid, int $exitCode, int $signal) {
        },

        // 工作进程退出事件（onWorkerExit）回调
        'hook_worker_exit' => function (Server $server, int $workerId) {
        },

        // 请求成功（onRequestSuccess）回调
        'hook_request_success' => function (Server $server, Request $request) {
        },

        // 请求错误（onRequestException）回调
        'hook_request_error' => function (Server $server, Request $request) {
        },
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
            'components' => ['log', 'pdo', 'redis', 'config', 'site', 'i18n'],
            'sleep' => 60,
        ]
    ],

    // 定时器配置
    'timer' => [
        //'crontab' => [
        //    'class' => App\Timer\CronTabProcess::class,
        //    'type' => Rid\Base\Timer::TICK,
        //    'msec' => 1 * 60 * 1000,
        //    'callback' => 'init'
        //]
    ],
];
