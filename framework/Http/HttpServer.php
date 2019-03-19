<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

use Rid\Base\TaskInterface;
use Rid\Base\Timer;
use Rid\Exceptions\TaskException;
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
        'enable_coroutine' => false,  // 开启协程
        'reactor_num' => 8,   // 主进程事件处理线程数
        'worker_num' => 8,  // 工作进程数
        'task_worker_num' => 8,  // 任务进程数
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

    // 初始化
    protected function initialize()
    {
        // 初始化参数
        $this->_host = $this->virtualHost['host'];
        $this->_port = $this->virtualHost['port'];
        $this->settings += $this->_settings;
        $this->createSever();
    }

    // 启动服务
    public function start()
    {
        $this->initialize();
        $this->welcome();
        $this->onStart();
        $this->onManagerStart();
        $this->onWorkerStart();
        $this->onTask();
        $this->onFinish();
        $this->onRequest();
        $this->_server->set($this->settings);
        $this->_server->start();
    }

    // 主进程启动事件
    protected function onStart()
    {
        $this->_server->on('Start', function (\swoole_server $server) {
            ProcessHelper::setTitle("rid-httpd: master {$this->_host}:{$this->_port}");
        });
    }

    // 管理进程启动事件
    protected function onManagerStart()
    {
        $this->_server->on('ManagerStart', function (\swoole_server $server) {
            // 进程命名
            ProcessHelper::setTitle("rid-httpd: manager");
        });
    }

    // 工作进程启动事件
    protected function onWorkerStart()
    {
        $this->_server->on('WorkerStart', function (\swoole_server $server,int $workerId) {
            // 刷新OpCode缓存，防止reload重载入时受到影响
            foreach (['apc_clear_cache', 'opcache_reset'] as $func) {
                if (function_exists($func)) $func();
            }

            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                ProcessHelper::setTitle("rid-httpd: worker #{$workerId}");
            } else {
                ProcessHelper::setTitle("rid-httpd: task #{$workerId}");
            }

            // 实例化App
            $config = require $this->virtualHost['configFile'];
            $app = new Application($config);
            $app->setServ($this->_server);
            $app->setWorker($workerId);
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
        });
    }

    protected function onTask()
    {
        $this->_server->on('Task', function (\swoole_server $serv, int $task_id, int $src_worker_id, $data) {
            $error_msg = "This Task {$task_id} from Worker {$src_worker_id}.\n";
            $data = unserialize($data);
            $task_worker_name = $data['worker'];
            if (class_exists($task_worker_name)) {
                /** @var TaskInterface $task_worker */
                $task_worker = new $task_worker_name();
                if ($task_worker instanceof TaskInterface) {
                    return $task_worker->run($data);
                }
                $error_msg .= "Error: The task worker is not instanceof TaskInterface,\nData: " . json_encode($data);
                throw new TaskException($error_msg);
            }
            $error_msg .= "No Task Worker model found,\nData: " . json_encode($data);
            throw new TaskException($error_msg);
        });
    }

    protected function onFinish() {
        $this->_server->on('Finish', function (\swoole_server $serv, $task_id, $data) {
            //echo "Task#$task_id finished, data_len=".strlen($data).PHP_EOL;
        });
    }

    // 请求事件
    protected function onRequest()
    {
        $this->_server->on('request', function (\swoole_http_request $request,\swoole_http_response $response) {
            try {
                app()->request->setRequester($request);
                app()->response->setResponder($response);
                app()->run();  // 执行请求
            } catch (\Throwable $e) {
                app()->error->handleException($e);
            }
        });
    }

    protected function createSever() {
        // 实例化服务器
        $this->_server = new \Swoole\Http\Server($this->_host, $this->_port);

        // rid-httpd 模式下，在此处创建全局的 \Swoole\Table
        $configTable = new \Swoole\Table(2048);
        $configTable->column('data', \Swoole\Table::TYPE_STRING, 256);
        $configTable->create();
        $this->_server->configTable = $configTable;
    }

    // 欢迎信息
    protected function welcome()
    {
        println(<<<EOL
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
        println('Server      Name:      rid-httpd');
        println('System      Name:      ' . strtolower(PHP_OS));
        println('Framework   Version:   ' . \Rid::VERSION);
        println('PHP         Version:   ' . PHP_VERSION);
        println('Swoole      Version:   ' . swoole_version());
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
