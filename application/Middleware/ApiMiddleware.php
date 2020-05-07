<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/11
 * Time: 16:15
 */

namespace App\Middleware;

use Rid\Http\Middleware\AbstractMiddleware;

class ApiMiddleware extends AbstractMiddleware
{
    public function handle($callable, \Closure $next)
    {
        // No cache for Api response
        app()->response->headers->set('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        app()->response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        app()->response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        app()->response->headers->set('Pragma', 'no-cache');

        if (env('APP_DEBUG')) {
            app()->response->headers->set('access-control-allow-origin', '*');
        }

        return $next();
    }
}
