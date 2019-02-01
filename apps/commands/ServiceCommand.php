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

    // 是否后台运行
    public $daemon = false;

    // 是否热更新
    public $update = false;

    // PID 文件
    protected $pidFile;

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
        // 设置pidfile
        $this->pidFile = '/var/run/rid-httpd.pid';
    }

    // 启动服务
    public function actionStart()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            app()->output->writeln("rid-httpd is running, PID : {$pid}.");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        $server = \Rid\Http\HttpServer::newInstanceByConfig('libraries.httpServer');
        if ($this->update) {
            $server->settings['max_request'] = 1;
        }
        $server->settings['daemonize'] = $this->daemon;
        $server->settings['pid_file'] = $this->pidFile;
        $server->start();
        // 返回退出码
        return ExitCode::OK;
    }

    // 停止服务
    public function actionStop()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid);
            while (ProcessHelper::isRunning($pid)) {
                // 等待进程退出
                usleep(100000);
            }
            app()->output->writeln('rid-httpd stop completed.');
        } else {
            app()->output->writeln('rid-httpd is not running.');
        }
        // 返回退出码
        return ExitCode::OK;
    }

    // 重启服务
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        // 返回退出码
        return ExitCode::OK;
    }

    // 重启工作进程
    public function actionReload()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            ProcessHelper::kill($pid, SIGUSR1);
        }
        if (!$pid) {
            app()->output->writeln('rid-httpd is not running.');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        app()->output->writeln('rid-httpd worker process restart completed.');
        // 返回退出码
        return ExitCode::OK;
    }

    // 查看服务状态
    public function actionStatus()
    {
        if ($pid = ProcessHelper::readPidFile($this->pidFile)) {
            app()->output->writeln("rid-httpd is running, PID : {$pid}.");
        } else {
            app()->output->writeln('rid-httpd is not running.');
        }
        // 返回退出码
        return ExitCode::OK;
    }

}
