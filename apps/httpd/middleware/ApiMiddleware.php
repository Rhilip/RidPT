<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/11
 * Time: 16:15
 */

namespace apps\httpd\middleware;


class ApiMiddleware
{
    public function handle($callable, \Closure $next)
    {
        app()->response->format = \mix\http\Response::FORMAT_JSON;
        return $next();
    }
}