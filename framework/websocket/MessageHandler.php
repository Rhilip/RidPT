<?php

namespace mix\websocket;

use mix\base\Component;

/**
 * SessionReader组件
 * @author 刘健 <coder.liu@qq.com>
 */
class MessageHandler extends Component
{

    // 控制器命名空间
    public $controllerNamespace = '';

    // 路由规则
    public $rules = [];

    // 服务
    protected $_server;

    // 文件描述符
    protected $_fd;

    // 设置服务
    public function setServer($server)
    {
        $this->_server = $server;
        return $this;
    }

    // 设置文件描述符
    public function setFd($fd)
    {
        $this->_fd = $fd;
        return $this;
    }

    // 执行功能
    public function runAction($action, $params = [])
    {
        // 匹配成功
        if (isset($this->rules[$action])) {
            // 实例化控制器
            list($shortClass, $shortAction) = $this->rules[$action];
            $shortClass       = str_replace('/', "\\", $shortClass);
            $controllerDir    = \mix\helpers\FileSystemHelper::dirname($shortClass);
            $controllerDir    = $controllerDir == '.' ? '' : "$controllerDir\\";
            $controllerName   = \mix\helpers\FileSystemHelper::basename($shortClass);
            $controllerClass  = "{$this->controllerNamespace}\\{$controllerDir}{$controllerName}Controller";
            $controllerAction = "action{$shortAction}";
            // 判断类是否存在
            if (class_exists($controllerClass)) {
                $controllerInstance = new $controllerClass([
                    'server' => $this->_server,
                    'fd'     => $this->_fd,
                ]);
                // 判断方法是否存在
                if (method_exists($controllerInstance, $controllerAction)) {
                    // 执行控制器的方法
                    $content = call_user_func_array([$controllerInstance, $controllerAction], $params);
                    // 响应
                    if (!is_null($content)) {
                        $this->_server->push($this->_fd, $content);
                    }
                    return;
                }
            }
        }
        throw new \mix\exceptions\NotFoundException("ERRER unknown action '{$action}'");
    }

}
