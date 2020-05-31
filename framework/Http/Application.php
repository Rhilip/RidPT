<?php

namespace Rid\Http;

use Rid\Component\Context;

use FastRoute\Dispatcher;
use Rid\Helpers\IoHelper;
use Rid\Http\Route\Exception\RouteException;

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
        // TODO replace by cachedDispatcher
        $this->dispatcher = \FastRoute\simpleDispatcher($this->config['routes'], [
            'routeCollector' => \Rid\Http\Route\RouteCollector::class,
        ]);
    }

    // 执行功能
    public function run(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->container->get('request')->setRequester($request);
        $this->container->get('response')->setResponder($response);
        $server = $this->container->get('request')->server->all();

        try {
            // 执行控制器并返回结果
            $content = $this->runAction(strtoupper($server['REQUEST_METHOD']), $server['PATH_INFO']);
        } catch (\Exception $e) {
            // 判定异常是来自路由的还是控制器的，控制器（未能捕捉的）抛出500
            $statusCode = $e instanceof RouteException ? $e->getCode() : 500;
            $this->container->get('response')->setStatusCode($statusCode);
            $content = $this->parseException($e);
        } finally {
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
    }

    // 执行功能并返回
    public function runAction($method, $path)
    {
        // 路由匹配
        $routeInfo = $this->dispatcher->dispatch($method, $path);
        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                $this->container->get('request')->attributes->set('route', $vars);

                // 执行中间件和控制器，并返回结果
                return $this->runWithMiddleware([$handler[0], $handler[1]], $handler['middlewares']);
            case Dispatcher::METHOD_NOT_ALLOWED:
                //$allowedMethods = $routeInfo[1];
                throw new RouteException('METHOD NOT ALLOWED', 405);
                break;
            case Dispatcher::NOT_FOUND:
            default:
                throw new RouteException('Not Found', 404);
                break;
        }
    }

    // 执行中间件
    protected function runWithMiddleware($callable, $middleware)
    {
        $middleware_class = array_shift($middleware);
        if (null === $middleware_class) {
            // Create the controller
            $controllerObject = $this->container->make($callable[0]);
            return $this->container->call([$controllerObject, $callable[1]]);
        }

        $item = $this->container->make($middleware_class);
        return $item->handle($callable, function () use ($callable, $middleware) {
            return $this->runWithMiddleware($callable, $middleware);
        });
    }

    protected function parseException(\Throwable $e)
    {
        $errors = [
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'type' => get_class($e),
            'trace' => $e->getTraceAsString(),
        ];

        // 日志处理
        if (!($e instanceof RouteException)) {
            $message = "{$errors['message']}" . PHP_EOL;
            $message .= "[type] {$errors['type']} [code] {$errors['code']}" . PHP_EOL;
            $message .= "[file] {$errors['file']} [line] {$errors['line']}" . PHP_EOL;
            $message .= "[trace] {$errors['trace']}" . PHP_EOL;
            $message .= '$_SERVER' . substr(print_r($this->container->get('request')->server->all() + $this->container->get('request')->headers->all(), true), 5);
            $message .= '$_GET' . substr(print_r($this->container->get('request')->query->all(), true), 5);
            $message .= '$_POST' . substr(print_r($this->container->get('request')->request->all(), true), 5, -1);
            $message .= PHP_EOL . 'Memory used: ' . memory_get_usage();
            IoHelper::getIo()->error($message);
            $this->container->get('logger')->error($message);
        }
        // 清空系统错误
        ob_get_contents() and ob_clean();

        return $this->container->get('view')->render('error', $errors);
    }
}
