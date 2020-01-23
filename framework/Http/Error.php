<?php

namespace Rid\Http;

use Rid\Base\Component;

/**
 * Error类
 */
class Error extends Component
{
    // 异常处理
    public function handleException(\Throwable $e)
    {
        $errors     = [
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'type'    => get_class($e),
            'trace'   => $e->getTraceAsString(),
        ];

        if (app()->response->getResponderStatus() !== false) {  // 在Web环境，存在 \Swoole\Http\Response 对象
            // debug处理 & exit处理
            if ($e instanceof \Rid\Exceptions\DebugException || $e instanceof \Rid\Exceptions\EndException) {
                \Rid::app()->response->setContent($e->getMessage());
                \Rid::app()->response->prepare(\Rid::app()->request);
                \Rid::app()->response->send();
                return;
            }
            // 错误参数定义
            $statusCode = $e instanceof \Rid\Exceptions\NotFoundException ? 404 : 500;
            $errors['status'] = $statusCode;
            // 日志处理
            if (!($e instanceof \Rid\Exceptions\NotFoundException)) {
                $message = "{$errors['message']}" . PHP_EOL;
                $message .= "[type] {$errors['type']} [code] {$errors['code']}" . PHP_EOL;
                $message .= "[file] {$errors['file']} [line] {$errors['line']}" . PHP_EOL;
                $message .= "[trace] {$errors['trace']}" . PHP_EOL;
                $message .= '$_SERVER' . substr(print_r(\Rid::app()->request->server->all() + \Rid::app()->request->headers->all(), true), 5);
                $message .= '$_GET' . substr(print_r(\Rid::app()->request->query->all(), true), 5);
                $message .= '$_POST' . substr(print_r(\Rid::app()->request->request->all(), true), 5, -1);
                $message .= 'Memory used: ' . memory_get_usage();
                println($message);
                app()->log->error($message);
            }
            // 清空系统错误
            ob_get_contents() and ob_clean();

            app()->response->setStatusCode($statusCode);
            app()->response->setContent(app()->view->render('error', $errors));
            \Rid::app()->response->prepare(\Rid::app()->request);

            app()->response->send();
        } else {  // 在Task或Timer环境 （使用 Console\Error的处理方法）
            if ($e instanceof \Rid\Exceptions\DebugException) {
                $content = $e->getMessage();
                println($content);
            }

            // 格式化输出
            $message = $errors['message'] . PHP_EOL;
            $message .= "{$errors['type']} code {$errors['code']}" . PHP_EOL;
            $message .= $errors['file'] . ' line ' . $errors['line'] . PHP_EOL;
            $message .= str_replace("\n", PHP_EOL, $errors['trace']) . PHP_EOL;

            // 日志处理
            if (!($e instanceof \Rid\Exceptions\NotFoundException)) {
                $log_message = $message . '$_SERVER' . substr(print_r($_SERVER, true), 5, -1);
                \Rid::app()->log->error($log_message);
            }
            // 清空系统错误
            ob_get_contents() and ob_clean();

            // 增加边距 写入stdout
            $message = str_repeat(' ', 4) . str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', 4), $message);
            println((PHP_EOL . PHP_EOL) . $message . (PHP_EOL));
        }
    }
}
