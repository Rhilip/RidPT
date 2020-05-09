<?php

namespace Rid\Http;

use Rid\Component\Runtime;
use Rid\Utils\Text;

/**
 * App类
 *
 */
class Application extends \Rid\Base\Application
{

    // 控制器命名空间
    public $controllerNamespace = '';

    // 全局中间件
    public $middleware = [];

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->controllerNamespace = $config['controllerNamespace'];  // FIXME
        $this->middleware = $config['middleware'];
    }

    // 执行功能
    public function run(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->container->get('request')->setRequester($request);
        $this->container->get('response')->setResponder($response);
        $server = $this->container->get('request')->server->all();
        $method = strtoupper($server['REQUEST_METHOD']);
        $action = empty($server['PATH_INFO']) ? '' : substr($server['PATH_INFO'], 1);

        // 执行控制器并返回结果
        $content = $this->runAction($method, $action);
        if (is_array($content)) {
            $this->container->get('response')->setJson($content);
        } else {
            $this->container->get('response')->setContent($content);
        }

        // 准备请求并发送
        $this->container->get('response')->prepare($this->container->get('request'));
        $this->container->get('response')->send();

        // 清扫Runtime组件容器
        $this->container->get(Runtime::class)->cleanContext();
    }

    // 执行功能并返回
    public function runAction($method, $action)
    {
        $action = "{$method} {$action}";
        // 路由匹配
        $result = $this->container->get('route')->match($action);
        foreach ($result as $item) {
            list($route, $queryParams) = $item;
            // 路由参数导入请求类
            $this->container->get('request')->attributes->set('route', $queryParams);
            // 实例化控制器
            list($shortClass, $shortAction) = $route;
            $controllerDir    = \Rid\Helpers\FileSystemHelper::dirname($shortClass);
            $controllerDir    = $controllerDir == '.' ? '' : "$controllerDir\\";
            $controllerName   = Text::toPascalName(\Rid\Helpers\FileSystemHelper::basename($shortClass));
            $controllerClass  = "{$this->controllerNamespace}\\{$controllerDir}{$controllerName}Controller";
            $shortAction      = Text::toPascalName($shortAction);
            $controllerAction = "action{$shortAction}";
            // 判断类是否存在
            if (class_exists($controllerClass)) {
                $controllerInstance = $this->container->get($controllerClass);
                // 判断方法是否存在
                if (method_exists($controllerInstance, $controllerAction)) {
                    // 执行中间件
                    $middleware = $this->newMiddlewareInstance($route['middleware']);
                    if (!empty($middleware)) {
                        return $this->runMiddleware([$controllerInstance, $controllerAction], $middleware);
                    }
                    // 直接返回执行结果
                    return $controllerInstance->$controllerAction();
                }
            }
            // 不带路由参数的路由规则找不到时，直接抛出错误
            if (empty($queryParams)) {
                break;
            }
        }
        throw new \Rid\Exceptions\NotFoundException('Not Found (#404)');
    }

    // 执行中间件
    protected function runMiddleware($callable, $middleware)
    {
        $item = array_shift($middleware);
        if (empty($item)) {
            return $this->container->call($callable);
        }
        return $item->handle($callable, function () use ($callable, $middleware) {
            return $this->runMiddleware($callable, $middleware);
        });
    }

    // 实例化中间件
    protected function newMiddlewareInstance($routeMiddleware)
    {
        $middleware = [];
        foreach (array_merge($this->middleware, $routeMiddleware) as $key => $class) {
            $middleware[$key] = $this->container->make($class);
        }
        return $middleware;
    }
}
