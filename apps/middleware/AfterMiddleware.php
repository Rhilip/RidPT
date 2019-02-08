<?php

namespace apps\middleware;

/**
 * 后置中间件
 */
class AfterMiddleware
{

    public function handle($callable, \Closure $next)
    {
        // 添加中间件执行代码
        $response = $next();
        list($controller, $action) = $callable;

        if (env('APP_DEBUG')) {
            println('NOW() :' . date('Y-m-d H:i:s')) ;
            echo 'SQL query list: ';
            var_dump(app()->pdo->getExecuteData());
            echo 'Redis Hits: ';
            var_dump(app()->redis->getCalledData());
            echo 'Memory used: ';
            var_dump(memory_get_usage());
        }

        // ...
        // 返回响应内容
        return $response;
    }

}
