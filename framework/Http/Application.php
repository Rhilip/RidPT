<?php

namespace Rid\Http;

use Rid\Base\Component;
use Swoole\Http\Server;

/**
 * App类
 *
 * @property \Rid\Http\Error $error
 * @property \Rid\Http\Session $session
 * @property \Rid\Http\Route $route
 * @property \Rid\Http\Request $request
 * @property \Rid\Http\Response $response
 * @property \App\Components\Auth $auth
 */
class Application extends \Rid\Base\Application
{

    // 控制器命名空间
    public $controllerNamespace = '';

    // 全局中间件
    public $middleware = [];

    protected $_serv;
    protected $_worker;

    // 执行功能
    public function run()
    {
        $server                        = \Rid::app()->request->server->all();
        $method                        = strtoupper($server['REQUEST_METHOD']);
        $action                        = empty($server['PATH_INFO']) ? '' : substr($server['PATH_INFO'], 1);
        \Rid::app()->response->setContent($this->runAction($method, $action));
        \Rid::app()->response->prepare(\Rid::app()->request);
        \Rid::app()->response->send();
    }

    // 执行功能并返回
    public function runAction($method, $action)
    {
        $action = "{$method} {$action}";
        // 路由匹配
        $result = \Rid::app()->route->match($action);
        foreach ($result as $item) {
            list($route, $queryParams) = $item;
            // 路由参数导入请求类
            \Rid::app()->request->setRoute($queryParams);
            // 实例化控制器
            list($shortClass, $shortAction) = $route;
            $controllerDir    = \Rid\Helpers\FileSystemHelper::dirname($shortClass);
            $controllerDir    = $controllerDir == '.' ? '' : "$controllerDir\\";
            $controllerName   = \Rid\Helpers\NameHelper::snakeToCamel(\Rid\Helpers\FileSystemHelper::basename($shortClass), true);
            $controllerClass  = "{$this->controllerNamespace}\\{$controllerDir}{$controllerName}Controller";
            $shortAction      = \Rid\Helpers\NameHelper::snakeToCamel($shortAction, true);
            $controllerAction = "action{$shortAction}";
            // 判断类是否存在
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass();
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
            return call_user_func($callable);
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
            $middleware[$key] = new $class();
        }
        return $middleware;
    }

    // 获取组件
    public function __get($name)
    {
        // 获取全名
        if (!is_null($this->_componentPrefix)) {
            $name = "{$this->_componentPrefix}.{$name}";
        }
        $this->setComponentPrefix(null);
        /* 常驻模式 */
        // 返回单例
        if (isset($this->_components[$name])) {
            // 触发请求前置事件
            $this->triggerRequestBefore($this->_components[$name]);
            // 返回对象
            return $this->_components[$name];
        }
        return $this->_components[$name];
    }

    // 装载全部组件
    public function loadAllComponents($components = null)
    {
        $components = $components ?? $this->components;
        foreach (array_keys($components) as $name) {
            $this->loadComponent($name);
        }
    }

    // 清扫组件容器
    public function cleanComponents()
    {
        // 触发请求后置事件
        foreach ($this->_components as $component) {
            $this->triggerRequestAfter($component);
        }
    }

    /** 触发请求前置事件
     * @param \Rid\Base\Component $component
     */
    protected function triggerRequestBefore($component)
    {
        if ($component->getStatus() == Component::STATUS_READY) {
            $component->onRequestBefore();
        }
    }

    /** 触发请求后置事件
     * @param \Rid\Base\Component $component
     */
    protected function triggerRequestAfter($component)
    {
        if ($component->getStatus() == Component::STATUS_RUNNING) {
            $component->onRequestAfter();
        }
    }

    // 获取公开目录路径
    public function getPublicPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    public function getStoragePath($sub_folder = null)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage' . (is_null($sub_folder) ? '' : DIRECTORY_SEPARATOR . $sub_folder);
    }

    // 打印变量的相关信息
    public function dump($var, $send = false)
    {
        ob_start();
        var_dump($var);
        $dumpContent                   = ob_get_clean();
        \Rid::app()->response->setContent(\Rid::app()->response->getContent() . $dumpContent);
        if ($send) {
            throw new \Rid\Exceptions\DebugException(\Rid::app()->response->getContent());
        }
    }

    // 终止程序
    public function end($content = '')
    {
        throw new \Rid\Exceptions\EndException($content);
    }

    /**
     * @return Server
     */
    public function getServ()
    {
        return $this->_serv;
    }

    /**
     * @param Server $serv
     */
    public function setServ(Server $serv): void
    {
        $this->_serv = $serv;
    }
}
