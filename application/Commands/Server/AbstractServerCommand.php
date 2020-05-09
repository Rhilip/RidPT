<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Commands\Server;

use App\Commands\AbstractCommand;

use Rid\Helpers\ContainerHelper;
use Rid\Http\Application;
use Rid\Helpers\ProcessHelper;
use Rid\Swoole\Helper\ServerHelper;
use Rid\Swoole\Memory;

use Rid\Swoole\Process\Process;
use Rid\Swoole\Timer\Timer;
use Swoole\Server;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractServerCommand extends AbstractCommand
{
    protected ?array $httpServerConfig = null;

    protected ?Server $server = null;

    // Don't change server setting here, Since All server setting will override by file `config/httpServer`
    protected array $serverSetting = [
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

    protected function configure(): void
    {
        $this->addOption('daemon', 'd', InputOption::VALUE_NONE, 'Run Server in daemon mode.')
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Run Server in hot mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $this->prepareServerConfig($input);
    }

    protected function prepareServerConfig(InputInterface $input)
    {
        $this->httpServerConfig = require RIDPT_ROOT . '/config/httpServer.php';
        $this->prepareServerRuntimeSetting($input);
    }

    protected function prepareServerRuntimeSetting(InputInterface $input)
    {
        $this->serverSetting = array_merge(
            $this->serverSetting,  // 默认设置
            $this->httpServerConfig['settings'],   // 用户配置文件
            ['enable_coroutine' => false] // FIXME 关闭默认协程，回调中有手动开启支持上下文的协程
        );

        // 根据input更新设置
        if ($input->getOption('update')) {
            $this->httpServerConfig['settings']['max_request'] = 1;
        }
        $this->httpServerConfig['settings']['daemonize'] = (int)$input->getOption('daemon');
    }

    protected function prepareServer()
    {
        if ($pid = $this->getPid()) {
            $this->io->error("rid-httpd is running, PID : {$pid}.");
            exit(1);
        }

        $this->server = new \Swoole\Http\Server($this->httpServerConfig['host'], $this->httpServerConfig['port']);
        $this->server->set($this->serverSetting);  // 设置服务器
        $this->bindServerEvents();  // 绑定事件

        ServerHelper::setServer($this->server);

        // FIXME 增加自定义进程
        $this->addCustomProcess();

        Memory\TableManager::init($this->httpServerConfig['table']);  // 创建全局的 \Swoole\Table
        Memory\AtomicManager::init($this->httpServerConfig['atomic'] ?? []);  // 创建全局的 \Swoole\Atomic

        return $this->server;
    }

    protected function startServer()
    {
        $this->printEnvironmentTable();
        $this->server->start();
    }

    protected function printEnvironmentTable()
    {
        $this->io->table(['Environment', ''], [
            ['[System]'],
            ['System      Name      ', defined('PHP_OS_FAMILY') ? PHP_OS_FAMILY : PHP_OS],
            ['CPU         Cores     ', swoole_cpu_num()],
            ['Disk        (Free/Total)', round(@disk_free_space('.') / (1024*1024*1024), 3). ' GB / ' . round(@disk_total_space('.') / (1024*1024*1024), 3). ' GB'],
            ['Network               ', implode(',', swoole_get_local_ip())],
            ['[Project]'],
            ['PHP         Version   ', PHP_VERSION],
            ['Swoole      Version   ', SWOOLE_VERSION],
            ['Framework   Version   ', PROJECT_VERSION],
            ['[Config]'],
            ['Config      File      ', $this->httpServerConfig['configFile']],
            ['Listen      Addr      ', $this->httpServerConfig['host']],
            ['Listen      Port      ', $this->httpServerConfig['port']],
            ['Reactor     Num       ', $this->serverSetting['reactor_num']],
            ['Worker      Num       ', $this->serverSetting['worker_num']],
            ['Task Worker Num       ', $this->serverSetting['task_worker_num']],
            ['Hot         Update    ', ($this->serverSetting['max_request'] == 1 ? 'enabled' : 'disabled')],
            ['Coroutine   Mode      ', ($this->serverSetting['enable_coroutine'] ? 'enabled' : 'disabled')],
        ]);
    }

    protected function bindServerEvents()
    {
        /**
         * 主进程启动事件
         * 仅允许echo、打印Log、修改进程名称，不得执行其他操作
         */
        $this->server->on('start', function (Server $server) {
            ProcessHelper::setTitle(PROJECT_NAME . ": Master {$this->httpServerConfig['host']}:{$this->httpServerConfig['port']}");
            // 执行回调
            $this->httpServerConfig['hook']['hook_start'] and call_user_func($this->httpServerConfig['hook']['hook_start'], $server);
        });

        /**
         * 主进程停止事件
         * 请勿在onShutdown中调用任何异步或协程相关API，触发onShutdown时底层已销毁了所有事件循环设施
         */
        $this->server->on('shutdown', function (Server $server) {
            // 执行回调
            $this->httpServerConfig['hook']['hook_shutdown'] and call_user_func($this->httpServerConfig['hook']['hook_shutdown'], $server);
        });

        /**
         * 管理进程启动事件
         * 可以使用基于信号实现的同步模式定时器swoole_timer_tick，不能使用task、async、coroutine等功能
         */
        $this->server->on('managerStart', function (Server $server) {
            ProcessHelper::setTitle(PROJECT_NAME . ": Manager");  // 进程命名
            // 执行回调
            $this->httpServerConfig['hook']['hook_manager_start'] and call_user_func($this->httpServerConfig['hook']['hook_manager_start'], $server);
        });

        /**
         * 管理进程停止事件
         */
        $this->server->on('managerStop', function (Server $server) {
            // 执行回调
            $this->httpServerConfig['hook']['hook_manager_stop'] and call_user_func($this->httpServerConfig['hook']['hook_manager_stop'], $server);
        });

        /**
         * FIXME 工作进程启动事件
         */
        $this->server->on('workerStart', function (\Swoole\Http\Server $server, int $workerId) {
            // 进程命名
            if ($workerId < $server->setting['worker_num']) {
                ProcessHelper::setTitle("rid-httpd: HTTP Worker #{$workerId}");
            } else {
                ProcessHelper::setTitle("rid-httpd: Task #{$workerId}");
            }

            // 实例化App
            $this->prepareApplication();

            if ($workerId == 0) {  // 将系统设置中的 Timer 添加到 worker #0 中
                foreach ($this->httpServerConfig['timer'] as $timer_name => $timer_config) {
                    $timer_class = $timer_config['class'];
                    $timer = new $timer_class();
                    if ($timer instanceof Timer) {
                        $timer->run($timer_config);
                    }
                }
            }

            // 执行回调
            $this->httpServerConfig['hook']['hook_worker_start'] and call_user_func($this->httpServerConfig['hook']['hook_worker_start'], $server, $workerId);
        });

        /**
         * 工作进程停止事件
         */
        $this->server->on('workerStop', function (Server $server, int $workerId) {
            // 执行回调
            $this->httpServerConfig['hook']['hook_worker_stop'] and call_user_func($this->httpServerConfig['hook']['hook_worker_stop'], $server, $workerId);
        });

        /**
         * 工作进程错误事件
         * 当Worker/Task进程发生异常后会在Manager进程内回调此函数。
         */
        $this->server->on('workerError', function (Server $server, int $workerId, int $workerPid, int $exitCode, int $signal) {
            // 执行回调
            $this->httpServerConfig['hook']['hook_worker_error'] and call_user_func($this->httpServerConfig['hook']['hook_worker_error'], $server, $workerId, $workerPid, $exitCode, $signal);
        });

        /**
         * 工作进程退出事件
         * 仅在开启reload_async特性后有效。异步重启特性，会先创建新的Worker进程处理新请求，旧的Worker进程自行退出
         */
        $this->server->on('workerExit', function (Server $server, int $workerId) {
            // 执行回调
            $this->httpServerConfig['hook']['hook_worker_exit'] and call_user_func($this->httpServerConfig['hook']['hook_worker_exit'], $server, $workerId);
        });

        /**
         * 请求事件
         */
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            try {
                app()->run($request, $response);  // 执行请求

                // 执行回调
                $this->httpServerConfig['hook']['hook_request_success'] and call_user_func($this->httpServerConfig['hook']['hook_request_success'], $this->server, $request);
            } catch (\Throwable $e) {
                \Rid\Helpers\ContainerHelper::getContainer()->get('error')->handleException($e);
                // 执行回调
                $this->httpServerConfig['hook']['hook_request_error'] and call_user_func($this->httpServerConfig['hook']['hook_request_error'], $this->server, $request);
            }
        });

        /**
         * Task事件
         */
        if (0 !== ($this->serverSetting['task_worker_num'] ?? -1)) {
            // 判断是不是启用了task_enable_coroutine， 因为启用前后onTask函数原型不同
            if ((!isset($this->serverSetting['enable_coroutine']) || $this->serverSetting['enable_coroutine'])
                && isset($this->serverSetting['task_enable_coroutine']) && $this->serverSetting['task_enable_coroutine']) {
                $this->server->on('task', function (Server $server, \Swoole\Server\Task $task) {
                    /** @var \Rid\Swoole\Task\TaskInfo $taskInfo */
                    $taskInfo = $task->data;
                    $result = $taskInfo->getHandler()->handle($taskInfo->getParams(), $server, $task->id, $task->worker_id);
                    $task->finish($result);
                });
            } else {
                $this->server->on('task', function (Server $server, int $taskID, int $workerID, $taskInfo) {
                    /** @var \Rid\Swoole\Task\TaskInfo $taskInfo */
                    $result = $taskInfo->getHandler()->handle($taskInfo->getParams(), $server, $taskID, $workerID);
                    $server->finish($result);
                });
            }
        }

        /**
         * Task Finish事件，
         * 空调用，请使用 TaskHandlerInterface::finish() 方法覆写
         */
        $this->server->on('finish', function (Server $server, int $taskID, $data) {
        });
    }

    protected function prepareApplication($components = null)
    {
        // FIXME 实例化App
        $config = require $this->httpServerConfig['configFile'];
        $app = new Application($config);

        // FIXME Container转移到APP中
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions(RIDPT_ROOT . '/config/components.php');
        $container = $builder->build();
        ContainerHelper::setContainer($container);

        $app->loadAllComponents($components);
    }

    private function addCustomProcess()
    {
        foreach ($this->httpServerConfig['process'] as $process_name => $process_config) {
            $process_class = $process_config['class'];
            $custom_process = new $process_class();
            if ($custom_process instanceof Process) {
                $process = new \Swoole\Process(function ($process) use ($process_name, $process_config, $custom_process) {
                    if ($process_config['title']) {
                        ProcessHelper::setTitle('rid-httpd: ' . $process_config['title']);
                    }

                    $this->prepareApplication(array_flip($process_config['components']));
                    $custom_process->start($process_config);
                });

                $this->server->addProcess($process);
            }
        }
    }

    protected function getPidFile()
    {
        return $this->httpServerConfig['settings']['pid_file'];
    }

    protected function getPid()
    {
        return ProcessHelper::readPidFile($this->getPidFile());
    }
}
