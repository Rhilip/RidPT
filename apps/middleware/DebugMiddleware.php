<?php

namespace apps\middleware;

class DebugMiddleware
{

    public function handle($callable, \Closure $next)
    {
        $response = $next();

        if (env('APP_DEBUG')) {
            println('NOW() :' . date('Y-m-d H:i:s')) ;
            echo 'SQL query list: ';
            var_dump(app()->pdo->getExecuteData());
            echo 'Redis Hits: ';
            var_dump(app()->redis->getCalledData());
            echo 'Memory used: ';
            var_dump(memory_get_usage());
        }

        return $response;
    }

}
