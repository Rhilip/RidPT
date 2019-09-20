<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 9/17/2019
 * Time: 2019
 */

namespace App\Commands;

use Ahc\Cli\Input\Command;

use Rid\Http\HttpServer;
use Rid\Helpers\ProcessHelper;

class ServerCommand extends Command
{
    protected $httpServerConfig;

    public function __construct()
    {
        parent::__construct('server', 'Manager Swoole Server');

        $this->argument('<action>', 'Action (start|stop|reload|taskreload|status) to Swoole Server (default \'status\')')
            ->option('-d --daemon', 'Run Server in daemon mode.', null, false)
            ->option('-u --update', 'Run Server in hot mode.', null, false);
    }

    public function execute($action, $update, $daemon)
    {
        $this->readConfig($update, $daemon);

        switch ($action) {
            case 'start':
                return $this->actionStart();
            case 'stop':
                return $this->actionStop();
            case 'restart':
                return $this->actionRestart();
            case 'reload':
                return $this->actionReload();
            case 'taskreload':
                return $this->actionTaskReload();
            case 'status':
            default:
                return $this->actionStatus();
        }
    }

    // 启动服务
    public function actionStart()
    {
        if ($pid = $this->getPid()) {
            println("rid-httpd is running, PID : {$pid}.");
            return 1;
        }

        $server = new HttpServer($this->httpServerConfig);
        $server->start();
        return 0;  // 返回退出码
    }

    // 停止服务
    public function actionStop()
    {
        if ($pid = $this->getPid()) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                usleep(100000);  // 等待进程退出
            }
            println('rid-httpd stop completed.');
        } else {
            println('rid-httpd is not running.');
        }
        return 0; // 返回退出码
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        return 0;  // 返回退出码
    }

    // 重启工作进程
    public function actionReload()
    {
        return $this->_reload();
    }

    // 重启任务进程
    public function actionTaskReload()
    {
        return $this->_reload(SIGUSR2);
    }

    // 查看服务状态
    public function actionStatus()
    {
        if ($pid = $this->getPid()) {
            println("rid-httpd is running, PID : {$pid}.");
        } else {
            println('rid-httpd is not running.');
        }
        return 0; // 返回退出码
    }

    private function _reload($sig = SIGUSR1)
    {
        if ($pid = $this->getPid()) {
            ProcessHelper::kill($pid, $sig);
        }
        if (!$pid) {
            println('rid-httpd is not running.');
            return 1;
        }
        println('rid-httpd ' . ($sig == SIGUSR1 ? 'worker' : 'task') . ' process reload completed.');
        return 0; // 返回退出码
    }

    // 载入并重写配置
    private function readConfig($update, $daemon)
    {
        $this->httpServerConfig = require RIDPT_ROOT . '/config/httpServer.php';
        if ($update) {
            $this->httpServerConfig['settings']['max_request'] = 1;
        }
        $this->httpServerConfig['settings']['daemonize'] = $daemon;
    }

    private function getPidFile()
    {
        return $this->httpServerConfig['settings']['pid_file'];
    }

    private function getPid()
    {
        return ProcessHelper::readPidFile($this->getPidFile());
    }
}
