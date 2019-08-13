<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

use Rid\Base\Process;
use Rid\Base\Timer;
use Rid\Helpers\ProcessHelper;

/**
 * Http服务器类
 */
class HttpServer extends BaseObject
{

    public $name = 'rid-httpd';

    // 虚拟主机
    public $virtualHost = [];

    // 运行参数
    public $settings = [];

    // 默认运行参数
    protected $_settings = [
        'enable_coroutine' => false,  // 开启协程
        'reactor_num' => 8,   // 主进程事件处理线程数
        'worker_num' => 8,  // 工作进程数
        'max_request' => 10000,      // 进程的最大任务数
        'reload_async' => true,    // 异步安全重启
        'max_wait_time' => 60,   // 退出等待时间
        'pid_file' => '/var/run/rid-httpd.pid',  // PID 文件
        'log_file' => '/tmp/rid-httpd.log',  // 日志文件路径
        'open_tcp_nodelay' => true,  // 开启后，PDOConnection 协程多次 prepare 才不会有 40ms 延迟
    ];

    // 服务器
    /** @var \Swoole\Http\Server */
    protected $_server;

    // 主机
    protected $_host;

    // 端口
    protected $_port;

    // 启动服务
    public function start()
    {
        // 初始化参数
        $this->_host = $this->virtualHost['host'];
        $this->_port = $this->virtualHost['port'];

        // 实例化服务器
        $this->_server = new \Swoole\Http\Server($this->_host, $this->_port);
        $this->settings += $this->_settings;
        $this->_server->set($this->settings);
        // 覆盖参数
        $this->_server->set([
            'enable_coroutine' => false, // 关闭默认协程，回调中有手动开启支持上下文的协程
        ]);

        // 绑定事件
        $this->_server->on('start', [$this, 'onStart']);
        $this->_server->on('shutdown', [$this, 'onShutdown']);
        $this->_server->on('managerStart', [$this, 'onManagerStart']);
        $this->_server->on('managerStop', [$this, 'onManagerStop']);
        $this->_server->on('workerStart', [$this, 'onWorkerStart']);
        $this->_server->on('workerStop', [$this, 'onWorkerStop']);
        $this->_server->on('workerError', [$this, 'onWorkerError']);
        $this->_server->on('workerExit', [$this, 'onWorkerExit']);
        $this->_server->on('request', [$this, 'onRequest']);

        // 增加自定义进程
        $this->addCustomProcess();

        // 欢迎信息
        $this->welcome();

        // 在此处创建全局的 \Swoole\Table
        $configTable = new \Swoole\Table(4096);
        $configTable->column('value' , \Swoole\Table::TYPE_STRING, 4096);
        $configTable->column('type', \Swoole\Table::TYPE_STRING, 64);
        $configTable->create();
        $this->_server->configTable = $configTable;

        return $this->_server->start();
    }

    /**
     * 主进程启动事件
     * 仅允许echo、打印Log、修改进程名称，不得执行其他操作
     * @param \Swoole\Server $server
     */
    public function onStart(\Swoole\Server $server)
    {
        ProcessHelper::setTitle("{$this->name}: master {$this->_host}:{$this->_port}");
    }

    /**
     * 主进程停止事件
     * 请勿在onShutdown中调用任何异步或协程相关API，触发onShutdown时底层已销毁了所有事件循环设施
     * @param \Swoole\Server $server
     */
    public function onShutdown(\Swoole\Server $server)
    {

    }

    /**
     * 管理进程启动事件
     * 可以使用基于信号实现的同步模式定时器swoole_timer_tick，不能使用task、async、coroutine等功能
     * @param \Swoole\Server $server
     */
    public function onManagerStart(\Swoole\Server $server)
    {
        ProcessHelper::setTitle("{$this->name}: manager");  // 进程命名
    }

    /**
     * 管理进程停止事件
     * @param \Swoole\Server $server
     */
    public function onManagerStop(\Swoole\Server $server)
    {

    }

    /**
     * 工作进程启动事件
     * @param \Swoole\Http\Server $server
     * @param int $workerId
     */
    public function onWorkerStart(\Swoole\Http\Server $server, int $workerId)
    {
        // 刷新OpCode缓存，防止reload重载入时受到影响
        foreach (['apc_clear_cache', 'opcache_reset'] as $func) {
            if (function_exists($func)) $func();
        }

        // 进程命名
        if ($workerId < $server->setting['worker_num']) {
            ProcessHelper::setTitle("rid-httpd: HTTP Worker #{$workerId}");
        } else {
            ProcessHelper::setTitle("rid-httpd: Task #{$workerId}");
        }

        // 实例化App
        $config = require $this->virtualHost['configFile'];
        $app = new Application($config);
        $app->setServ($this->_server);
        $app->setWorkerId($workerId);
        $app->loadAllComponents();

        if ($workerId == 0) {  // 将系统设置中的 Timer 添加到 worker #0 中
            foreach (app()->env('timer') as $timer_name => $timer_config) {
                $timer_class = $timer_config['class'];
                $timer = new $timer_class();
                if ($timer instanceof Timer) {
                    $timer->run($timer_config);
                }
            }
        }
    }

    /**
     * 工作进程停止事件
     * @param \Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerStop(\Swoole\Server $server, int $workerId)
    {

    }

    /**
     * 工作进程错误事件
     * 当Worker/Task进程发生异常后会在Manager进程内回调此函数。
     * @param \Swoole\Server $server
     * @param int $workerId
     * @param int $workerPid
     * @param int $exitCode
     * @param int $signal
     */
    public function onWorkerError(\Swoole\Server $server, int $workerId, int $workerPid, int $exitCode, int $signal)
    {

    }

    /**
     * 工作进程退出事件
     * 仅在开启reload_async特性后有效。异步重启特性，会先创建新的Worker进程处理新请求，旧的Worker进程自行退出
     * @param \Swoole\Server $server
     * @param int $workerId
     */
    public function onWorkerExit(\Swoole\Server $server, int $workerId)
    {

    }

    /**
     * 请求事件
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        try {
            app()->request->setRequester($request);
            app()->response->setResponder($response);
            app()->run();  // 执行请求
        } catch (\Throwable $e) {
            app()->error->handleException($e);
        }
    }

    private function addCustomProcess()
    {
        foreach (app()->env('process') as $process_name => $process_config) {
            $process_class = $process_config['class'];
            $custom_process = new $process_class();
            if ($custom_process instanceof Process) {
                $process = new \Swoole\Process(function ($process) use ($process_name, $process_config, $custom_process) {

                    if ($process_config['title']) ProcessHelper::setTitle('rid-httpd: ' . $process_config['title']);

                    // FIXME 实例化App
                    $config = require $this->virtualHost['configFile'];
                    $app = new Application($config);
                    $app->setServ($this->_server);
                    $app->loadAllComponents(array_flip($process_config['components']));

                    $custom_process->start($process_config);
                });

                $this->_server->addProcess($process);
            }
        }
    }

    // 欢迎信息
    protected function welcome()
    {
        println(<<<EOL
───────────────────────────────────────
                     ____            __  ____    ______   
                    /\  _`\   __    /\ \/\  _`\ /\__  _\  
                    \ \ \L\ \/\_\   \_\ \ \ \L\ \/_/\ \/  
                     \ \ ,  /\/\ \  /'_` \ \ ,__/  \ \ \  
                      \ \ \\ \\ \ \/\ \L\ \ \ \/    \ \ \ 
                       \ \_\ \_\ \_\ \___,_\ \_\     \ \_\
                        \/_/\/ /\/_/\/__,_ /\/_/      \/_/

EOL
        );
        println('───────────────────────────────────────');
        println('Server      Name:      ' . $this->name);
        println('System      Name:      ' . PHP_OS);
        println('Framework   Version:   ' . \Rid::VERSION);
        println('PHP         Version:   ' . PHP_VERSION);
        println('Swoole      Version:   ' . SWOOLE_VERSION);
        println('Listen      Addr:      ' . $this->_host);
        println('Listen      Port:      ' . $this->_port);
        println('Reactor     Num:       ' . $this->settings['reactor_num']);
        println('Worker      Num:       ' . $this->settings['worker_num']);
        println('Hot         Update:    ' . ($this->settings['max_request'] == 1 ? 'enabled' : 'disabled'));
        println('Coroutine   Mode:      ' . ($this->settings['enable_coroutine'] ? 'enabled' : 'disabled'));
        println('Config      File:      ' . $this->virtualHost['configFile']);
        println('───────────────────────────────────────');
    }

}
