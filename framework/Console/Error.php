<?php

namespace Rid\Console;

use Rid\Base\Component;

/**
 * Error类
 */
class Error extends Component
{

    // 错误级别
    public $level = E_ALL;

    // 异常处理
    public function handleException($e, $exit = false)
    {
        // debug处理
        if ($e instanceof \Rid\Exceptions\DebugException) {
            $content = $e->getMessage();
            echo $content;
            $this->exit(ExitCode::OK);
        }
        // exit处理
        if ($e instanceof \Rid\Exceptions\EndException) {
            $exitCode = (int)$e->getMessage();
            $this->exit($exitCode);
        }
        // 错误参数定义
        $errors = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];
        $time   = date('Y-m-d H:i:s');
        // 日志处理
        if (!($e instanceof \Rid\Exceptions\NotFoundException)) {
            $message = "{$errors['message']}" . PHP_EOL;
            $message .= "[type] {$errors['type']} [code] {$errors['code']}" . PHP_EOL;
            $message .= "[file] {$errors['file']} [line] {$errors['line']}" . PHP_EOL;
            $message .= "[trace] {$errors['trace']}" . PHP_EOL;
            $message .= '$_SERVER' . substr(print_r($_SERVER, true), 5, -1);
            \Rid::app()->log->error($message);
        }
        // 清空系统错误
        ob_get_contents() and ob_clean();
        // 格式化输出
        $message = $errors['message'] . PHP_EOL;
        $message .= "{$errors['type']} code {$errors['code']}" . PHP_EOL;
        $message .= $errors['file'] . ' line ' . $errors['line'] . PHP_EOL;
        $message .= str_replace("\n", PHP_EOL, $errors['trace']);
        // 增加边距
        $message = str_repeat(' ', 4) . str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', 4), $message);
        $message = (PHP_EOL . PHP_EOL) . $message . (PHP_EOL);
        // 写入
        println($message);
        println('');
        // 退出
        $exit and $this->exit(ExitCode::EXCEPTION);
    }

    // 退出
    protected function exit($exitCode)
    {
        exit($exitCode);
    }

}
