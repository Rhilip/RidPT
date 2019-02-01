<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

use Rid\Helpers\ProcessHelper;

/**
 * Http服务器类
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
        // 主进程事件处理线程数
        'reactor_num'      => 8,
        // 工作进程数
        'worker_num'       => 8,
        // 任务进程数
        'task_worker_num'  => 0,
        // 进程的最大任务数
        'max_request' => 10000,
        // 异步安全重启
        'reload_async' => true,
        // 退出等待时间
        'max_wait_time' => 60,
        // PID 文件
        'pid_file' => '/var/run/rid-httpd.pid',
        // 日志文件路径
        'log_file' => '/tmp/rid-httpd.log',
        // 开启后，PDOConnection 协程多次 prepare 才不会有 40ms 延迟
        'open_tcp_nodelay' => true,
    ];

    // 服务器
    /** @var \Swoole\Http\Server */
    protected $_server;

    // 主机
    protected $_host;

    // 端口
    protected $_port;

    // 初始化
    protected function initialize()
    {
        // 初始化参数
        $this->_host = $this->virtualHost['host'];
        $this->_port = $this->virtualHost['port'];
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
            ProcessHelper::setTitle("rid-httpd: master {$this->_host}:{$this->_port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->_server->on('ManagerStart', function ($server) {
            // 进程命名
            ProcessHelper::setTitle("rid-httpd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->_server->on('WorkerStart', function ($server, $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                ProcessHelper::setTitle("rid-httpd: worker #{$workerId}");
            } else {
                ProcessHelper::setTitle("rid-httpd: task #{$workerId}");
            }
            // 实例化App
            $config = require $this->virtualHost['configFile'];
            $app = new Application($config);
            $app->loadAllComponents();
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->_server->on('request', function ($request, $response) {
            // 执行请求
            try {
                \Rid::app()->request->setRequester($request);
                \Rid::app()->response->setResponder($response);
                \Rid::app()->run();
            } catch (\Throwable $e) {
                \Rid::app()->error->handleException($e);
            }
        });
    }

    // 欢迎信息
    protected function welcome()
    {
        echo <<<EOL
        
────────────────────────────────────────────────────────────────────────────
─████████████████───██████████─████████████───██████████████─██████████████─
─██░░░░░░░░░░░░██───██░░░░░░██─██░░░░░░░░████─██░░░░░░░░░░██─██░░░░░░░░░░██─
─██░░████████░░██───████░░████─██░░████░░░░██─██░░██████░░██─██████░░██████─
─██░░██────██░░██─────██░░██───██░░██──██░░██─██░░██──██░░██─────██░░██─────
─██░░████████░░██─────██░░██───██░░██──██░░██─██░░██████░░██─────██░░██─────
─██░░░░░░░░░░░░██─────██░░██───██░░██──██░░██─██░░░░░░░░░░██─────██░░██─────
─██░░██████░░████─────██░░██───██░░██──██░░██─██░░██████████─────██░░██─────
─██░░██──██░░██───────██░░██───██░░██──██░░██─██░░██─────────────██░░██─────
─██░░██──██░░██████─████░░████─██░░████░░░░██─██░░██─────────────██░░██─────
─██░░██──██░░░░░░██─██░░░░░░██─██░░░░░░░░████─██░░██─────────────██░░██─────
─██████──██████████─██████████─████████████───██████─────────────██████─────
────────────────────────────────────────────────────────────────────────────


EOL;
        app()->output->writeln('Server      Name:      rid-httpd');
        app()->output->writeln('System      Name:      ' . strtolower(PHP_OS));
        app()->output->writeln('Framework   Version:   ' . \Rid::VERSION);
        app()->output->writeln("PHP         Version:   " . PHP_VERSION);
        app()->output->writeln("Swoole      Version:   " . swoole_version());
        app()->output->writeln("Listen      Addr:      {$this->_host}");
        app()->output->writeln("Listen      Port:      {$this->_port}");
        app()->output->writeln('Reactor     Num:       ' . $this->settings['reactor_num']);
        app()->output->writeln('Worker      Num:       ' . $this->settings['worker_num']);
        app()->output->writeln('Hot         Update:    ' . ($this->settings['max_request'] == 1 ? 'enabled' : 'disabled'));
        app()->output->writeln('Coroutine   Mode:      ' . ($this->settings['enable_coroutine'] ? 'enabled' : 'disabled'));
        app()->output->writeln("Config      File:      {$this->virtualHost['configFile']}");
    }

}
