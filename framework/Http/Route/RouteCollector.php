<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Http\Route;

use FastRoute\RouteCollector as FastRouteCollector;

/**
 * Extend FastRoute\RouteCollector, So we can support middleware.
 *
 * Class RouteCollector
 * @package Rid\Http\Route
 */
class RouteCollector extends FastRouteCollector
{
    /** @var array List of middlewares called using the middleware() method. */
    private array $currentMiddlewares = [];

    /**
     * Encapsulate all the routes that are added from $func(Router) with this middleware.
     *
     * If the return value of the middleware is false, throws a RouteMiddlewareFailedException.
     *
     * @param string|string[] $middlewareClass The middleware to use
     * @param callable $callback
     */
    public function addMiddleware($middlewareClass, callable $callback): void
    {
        $previousMiddlewares = $this->currentMiddlewares;
        array_push($this->currentMiddlewares, ...(array)$middlewareClass);
        $callback($this);
        $this->currentMiddlewares = $previousMiddlewares;
    }

    public function addRoute($httpMethod, $route, $handler)
    {
        $handler['middlewares'] = $this->currentMiddlewares;
        parent::addRoute($httpMethod, $route, $handler);
    }
}
