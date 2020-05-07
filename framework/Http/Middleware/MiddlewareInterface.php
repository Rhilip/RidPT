<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/7/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Http\Middleware;

interface MiddlewareInterface
{
    public function handle($callable, \Closure $next);
}
