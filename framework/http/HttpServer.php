<?php

namespace mix\http;

use mix\base\BaseObject;
use mix\facades\Output;
use mix\helpers\ProcessHelper;

/**
 * Http服务器类
 * @author 刘健 <coder.liu@qq.com>
 */
class HttpServer extends BaseObject
{

    // 虚拟主机
    public $virtualHost = [];

    // 运行参数
    public $settings = [];

    // 默认运行参数
    protected $_settings = [
        // 开启协程
        'enable_coroutine' => false,
        // 进程的最大任务数
        'max_request'      => 10000,
        // 异步安全重启
        'reload_async'     => true,
        // 退出等待时间
        'max_wait_time'    => 60,
        // PID 文件
        'pid_file'         => '/var/run/mix-httpd.pid',
        // 日志文件路径
        'log_file'         => '/tmp/mix-httpd.log',
        // 开启后，PDO 协程多次 prepare 才不会有 40ms 延迟
        'open_tcp_nodelay' => true,
    ];

    // 服务器
    protected $_server;

    // 主机
    protected $_host;

    // 端口
    protected $_port;

    // 初始化
    protected function initialize()
    {
        // 初始化参数
        $this->_host    = $this->virtualHost['host'];
        $this->_port    = $this->virtualHost['port'];
        $this->settings += $this->_settings;
        // 实例化服务器
        $this->_server = new \Swoole\Http\Server($this->_host, $this->_port);
    }

    // 启动服务
    public function start()
    {
        $this->initialize();
        $this->welcome();
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onRequest();
        $this->_server->set($this->settings);
        $this->_server->start();
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->_server->on('Start', function ($server) {
            // 进程命名
            ProcessHelper::setTitle("mix-httpd: master {$this->_host}:{$this->_port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->_server->on('ManagerStart', function ($server) {
            // 进程命名
            ProcessHelper::setTitle("mix-httpd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->_server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                ProcessHelper::setTitle("mix-httpd: worker #{$workerId}");
            } else {
                ProcessHelper::setTitle("mix-httpd: task #{$workerId}");
            }
            // 实例化App
            $config = require $this->virtualHost['configFile'];
            $app    = new \mix\http\Application($config);
            $app->loadAllComponents();
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->_server->on('request', function ($request, $response) {
            // 执行请求
            try {
                \Mix::app()->request->setRequester($request);
                \Mix::app()->response->setResponder($response);
                \Mix::app()->run();
            } catch (\Throwable $e) {
                \Mix::app()->error->handleException($e);
            }
        });
    }

    // 欢迎信息
    protected function welcome()
    {
        $swooleVersion = swoole_version();
        $phpVersion    = PHP_VERSION;
        echo <<<EOL
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/


EOL;
        Output::writeln('Server      Name:      mix-httpd');
        Output::writeln('Framework   Version:   ' . \Mix::VERSION);
        Output::writeln("PHP         Version:   {$phpVersion}");
        Output::writeln("Swoole      Version:   {$swooleVersion}");
        Output::writeln("Listen      Addr:      {$this->_host}");
        Output::writeln("Listen      Port:      {$this->_port}");
        Output::writeln('Hot         Update:    ' . ($this->settings['max_request'] == 1 ? 'enabled' : 'disabled'));
        Output::writeln('Coroutine   Mode:      ' . ($this->settings['enable_coroutine'] ? 'enabled' : 'disabled'));
        Output::writeln("Config      File:      {$this->virtualHost['configFile']}");
    }

}
