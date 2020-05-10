<?php

namespace Rid\Http;

use Rid\Component\Context;

use FastRoute\Dispatcher;

/**
 * App类
 *
 */
class Application extends \Rid\Base\Application
{

    protected Dispatcher $dispatcher;

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->createRouteDispatcher();
    }

    protected function createRouteDispatcher()
    {
        $route = require RIDPT_ROOT . '/config/route.php';
        // TODO replace by cachedDispatcher
        $this->dispatcher = \FastRoute\simpleDispatcher($route, [
            'routeCollector' => \Rid\Http\Route\RouteCollector::class,
        ]);
    }

    // 执行功能
    public function run(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->container->get('request')->setRequester($request);
        $this->container->get('response')->setResponder($response);
        $server = $this->container->get('request')->server->all();

        // 执行控制器并返回结果
        $content = $this->runAction(strtoupper($server['REQUEST_METHOD']), $server['PATH_INFO']);
        if (is_array($content)) {
            $this->container->get('response')->setJson($content);
        } else {
            $this->container->get('response')->setContent($content);
        }

        // 准备请求并发送
        $this->container->get('response')->prepare($this->container->get('request'));
        $this->container->get('response')->send();

        // 清扫Runtime组件容器
        $this->container->get(Context::class)->cleanContext();
    }

    // 执行功能并返回
    public function runAction($method, $path)
    {
        // 路由匹配
        var_dump($method, $path);
        $routeInfo = $this->dispatcher->dispatch($method, $path);
        var_dump($routeInfo);
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $this->container->get('request')->attributes->set('route', $vars);

                // 执行中间件和控制器，并返回结果
                return $this->runMiddleware([$handler[0], $handler[1]], $handler['middlewares']);

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new \Rid\Exceptions\NotFoundException('Not Found (#404)');
                break;
            case Dispatcher::NOT_FOUND:
            default:
                throw new \Rid\Exceptions\NotFoundException('Not Found (#404)');
                break;
        }
    }

    // 执行中间件
    protected function runMiddleware($callable, $middleware)
    {
        $middleware_class = array_shift($middleware);
        if (null === $middleware_class) {
            // Create the controller
            $controllerObject = $this->container->make($callable[0]);
            return $this->container->call([$controllerObject, $callable[1]]);
        }

        $item = $this->container->make($middleware_class);
        return $item->handle($callable, function () use ($callable, $middleware) {
            return $this->runMiddleware($callable, $middleware);
        });
    }
}
