<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/11
 * Time: 16:15
 */

namespace apps\middleware;


class ApiMiddleware
{
    public function handle($callable, \Closure $next)
    {
        app()->response->format = \Rid\Http\Response::FORMAT_JSON;
        if (env("APP_DEBUG")) {
            app()->response->setHeader("access-control-allow-origin","*");
        }
        return $next();
    }
}
