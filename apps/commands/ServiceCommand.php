<?php

namespace apps\commands;

use Rid\Console\Command;
use Rid\Console\ExitCode;
use Rid\Helpers\ProcessHelper;

/**
 * Service 命令
 */
class ServiceCommand extends Command
{

    public $daemon = false; // 是否后台运行
    public $update = false; // 是否热更新

    protected $pidFile;  // PID 文件

    // 选项配置
    public function options()
    {
        return ['daemon', 'update'];
    }

    // 选项别名配置
    public function optionAliases()
    {
        return ['d' => 'daemon', 'u' => 'update'];
    }

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->pidFile = '/var/run/rid-httpd.pid';  // 设置pidfile
    }

    // 启动服务
    public function actionStart()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            println("rid-httpd is running, PID : {$pid}.");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $server = \Rid\Http\HttpServer::newInstanceByConfig('libraries.httpServer');
        if ($this->update) {
            $server->settings['max_request'] = 1;
        }
        $server->settings['daemonize'] = $this->daemon;
        $server->settings['pid_file'] = $this->pidFile;
        $server->start();
        return ExitCode::OK;  // 返回退出码
    }

    // 停止服务
    public function actionStop()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                usleep(100000);  // 等待进程退出
            }
            println('rid-httpd stop completed.');
        } else {
            println('rid-httpd is not running.');
        }
        return ExitCode::OK; // 返回退出码
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        return ExitCode::OK;  // 返回退出码
    }

    // 重启工作进程
    public function actionReload()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid, SIGUSR1);
        }
        if (!$pid) {
            println('rid-httpd is not running.');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        println('rid-httpd worker process restart completed.');
        return ExitCode::OK; // 返回退出码
    }

    // 查看服务状态
    public function actionStatus()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            println("rid-httpd is running, PID : {$pid}.");
        } else {
            println('rid-httpd is not running.');
        }
        return ExitCode::OK; // 返回退出码
    }

}
