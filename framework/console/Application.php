<?php

namespace mix\console;

/**
 * App类
 * @author 刘健 <coder.liu@qq.com>
 */
class Application extends \mix\base\Application
{

    // 命令命名空间
    public $commandNamespace = '';

    // 命令
    public $commands = [];

    // 执行功能 (CLI模式)
    public function run()
    {
        if (PHP_SAPI != 'cli') {
            throw new \RuntimeException('Please run in CLI mode.');
        }
        $input   = \Mix::app()->input;
        $command = $input->getCommand();
        $options = $input->getOptions();
        if (empty($command)) {
            throw new \mix\exceptions\NotFoundException("Please input command, '-h/--help' view help.");
        }
        if (in_array($command, ['-h', '--help'])) {
            $this->help();
            return ExitCode::OK;
        }
        if (in_array($command, ['-v', '--version'])) {
            $this->version();
            return ExitCode::OK;
        }
        return $this->runAction($command, $options);
    }

    // 帮助
    protected function help()
    {
        $input  = \Mix::app()->input;
        $output = \Mix::app()->output;
        $output->writeln("Usage: {$input->getScriptFileName()} [OPTIONS] [COMMAND [OPTIONS]]");
        $this->printOptions();
        $this->printCommands();
        $output->writeln('');
    }

    // 版本
    protected function version()
    {
        $input   = \Mix::app()->input;
        $output  = \Mix::app()->output;
        $version = \Mix::VERSION;
        $output->writeln("MixPHP Framework Version {$version}");
    }

    // 打印选项列表
    protected function printOptions()
    {
        $output = \Mix::app()->output;
        $output->writeln('');
        $output->writeln('Options:');
        $output->writeln("  -h/--help\tPrint usage.");
        $output->writeln("  -v/--version\tPrint version information.");
    }

    // 打印命令列表
    protected function printCommands()
    {
        $output = \Mix::app()->output;
        $output->writeln('');
        $output->writeln('Commands:');
        $prevPrefix = '';
        foreach ($this->commands as $command => $item) {
            $prefix = explode(' ', $command)[0];
            if ($prefix != $prevPrefix) {
                $prevPrefix = $prefix;
                $output->writeln('  ' . $prefix);
            }
            $output->write(str_repeat(' ', 4) . $command, Output::FG_GREEN);
            $output->writeln((isset($item['description']) ? "\t{$item['description']}" : ''), Output::NONE);
        }
    }

    // 执行功能并返回
    public function runAction($command, $options)
    {
        if (isset($this->commands[$command])) {
            // 实例化控制器
            list($shortClass, $shortAction) = $this->commands[$command];
            $shortClass    = str_replace('/', "\\", $shortClass);
            $commandDir    = \mix\helpers\FileSystemHelper::dirname($shortClass);
            $commandDir    = $commandDir == '.' ? '' : "$commandDir\\";
            $commandName   = \mix\helpers\FileSystemHelper::basename($shortClass);
            $commandClass  = "{$this->commandNamespace}\\{$commandDir}{$commandName}Command";
            $commandAction = "action{$shortAction}";
            // 判断类是否存在
            if (class_exists($commandClass)) {
                $commandInstance = new $commandClass($options);
                // 判断方法是否存在
                if (method_exists($commandInstance, $commandAction)) {
                    return $commandInstance->$commandAction();
                }
            }
        }
        throw new \mix\exceptions\NotFoundException("ERRER unknown command '{$command}'");
    }

    // 获取组件
    public function __get($name)
    {
        // 获取全名
        if (!is_null($this->_componentPrefix)) {
            $name = "{$this->_componentPrefix}.{$name}";
        }
        $this->setComponentPrefix(null);
        // 返回单例
        if (isset($this->_components[$name])) {
            // 返回对象
            return $this->_components[$name];
        }
        // 装载组件
        $this->loadComponent($name);
        // 返回对象
        return $this->_components[$name];
    }

    // 打印变量的相关信息
    public function dump($var, $send = false)
    {
        static $content = '';
        ob_start();
        var_dump($var);
        $dumpContent = ob_get_clean();
        $content     .= $dumpContent;
        if ($send) {
            throw new \mix\exceptions\DebugException($content);
        }
    }

    // 终止程序
    public function end($exitCode = ExitCode::OK)
    {
        throw new \mix\exceptions\EndException($exitCode);
    }

}
