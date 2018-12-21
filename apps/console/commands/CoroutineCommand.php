<?php

namespace apps\console\commands;

use mix\Base\Channel;
use mix\Base\ChannelHook;
use mix\Console\ExitCode;
use mix\Redis\PDO;
use mix\Facades\Input;
use mix\Facades\Output;

/**
 * 协程范例
 * @author 刘健 <coder.liu@qq.com>
 */
class CoroutineCommand extends BaseCommand
{

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize(); // TODO: Change the autogenerated stub
        // 获取程序名称
        $this->programName = Input::getCommandName();
        // 设置pidfile
        $this->pidFile = "/var/run/{$this->programName}.pid";
    }

    // 执行任务
    public function actionExec()
    {
        // 预处理
        parent::actionExec();
        // 执行
        $this->execute();
        // 返回退出码
        return ExitCode::OK;
    }

    // 执行
    public function execute()
    {
        // 并行查询数据
        tgo(function () {
            $time = time();
            // 并行查询数据
            list($foo, $bar) = [$this->foo(), $this->bar()];
            // 取出查询结果
            list($fooResult, $barResult) = [$foo->pop(), $bar->pop()];
            // 输出 time: 2，说明是并行执行
            Output::writeln('Time: ' . (time() - $time));
        });
    }

    // 查询数据
    public function foo()
    {
        $chan = new Channel();
        tgo(function (ChannelHook $hook) use ($chan) {
            // 安装钩子
            $hook->install($chan);
            // 子协程内只可使用局部变量，而组件为全局变量是不可以在子协程内使用的，会导致内存溢出，所以使用组件配置动态实例化
            $pdo    = PDO::newInstanceByConfig('libraries.[coroutine.pdo]');
            $result = $pdo->createCommand('select sleep(2)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

    // 查询数据
    public function bar()
    {
        $chan = new Channel();
        tgo(function (ChannelHook $hook) use ($chan) {
            // 安装钩子
            $hook->install($chan);
            // 子协程内只可使用局部变量，而组件为全局变量是不可以在子协程内使用的，会导致内存溢出，所以使用组件配置动态实例化
            $pdo    = PDO::newInstanceByConfig('libraries.[coroutine.pdo]');
            $result = $pdo->createCommand('select sleep(1)')->queryAll();
            $chan->push($result);
        });
        return $chan;
    }

}
