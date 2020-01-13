<?php

namespace Rid\Http;

use Rid\Base\Component;

/**
 * Route组件
 */
class Route extends Component
{

    // 默认变量规则
    public $defaultPattern = '[\w-]+';

    // 路由变量规则
    public $patterns = [];

    // 路由规则
    public $rules = [];

    // 转化后的路由规则
    protected $_rules = [];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->initialize();         // 初始化
    }

    // 初始化，生成路由数据，将路由规则转换为正则表达式，并提取路由参数名
    public function initialize()
    {
        // URL 目录处理
        $rules = [];
        foreach ($this->rules as $rule => $route) {
            $rules[$rule] = $route;
            if (strpos($rule, '{controller}') !== false && strpos($rule, '{action}') !== false) {
                $prev    = dirname($rule);
                $prevTwo = dirname($prev);
                $prevTwo = $prevTwo == '.' ? '' : $prevTwo;
                list($controller) = $route;
                // 增加上两级的路由
                $prevRules = [
                    $prev    => [$controller, 'Index'],
                    $prevTwo => [str_replace('{controller}', 'Index', $controller), 'Index'],
                ];
                // 附上中间件
                if (isset($route['middleware'])) {
                    $prevRules[$prev]['middleware']    = $route['middleware'];
                    $prevRules[$prevTwo]['middleware'] = $route['middleware'];
                }
                $rules += $prevRules;
            }
        }
        // 转正则
        foreach ($rules as $rule => $route) {
            if ($blank = strpos($rule, ' ')) {
                $method = substr($rule, 0, $blank);
                $method = "(?:{$method}) ";
                $rule   = substr($rule, $blank + 1);
            } else {
                $method = '(?:CLI|GET|POST|PUT|PATCH|DELETE|HEAD|OPTIONS) ';
            }
            $fragment = explode('/', $rule);
            $names    = [];
            foreach ($fragment as $k => $v) {
                preg_match('/{([\w-]+)}/i', $v, $matches);
                if (!empty($matches)) {
                    list($fname) = $matches;
                    if (isset($this->patterns[$fname])) {
                        $fragment[$k] = str_replace("{$fname}", "({$this->patterns[$fname]})", $fragment[$k]);
                    } else {
                        $fragment[$k] = str_replace("{$fname}", "({$this->defaultPattern})", $fragment[$k]);
                    }
                    $names[] = $fname;
                }
            }
            $pattern        = '/^' . $method . implode('\/', $fragment) . '\/*$/i';
            $this->_rules[] = [$pattern, $route, $names];
        }
    }

    // 匹配功能，由于路由歧义，会存在多条路由规则都可匹配的情况
    public function match($action)
    {
        // 匹配
        $result = [];
        foreach ($this->_rules as $item) {
            list($pattern, $route, $names) = $item;
            if (preg_match($pattern, $action, $matches)) {
                $queryParams = [];
                // 提取路由查询参数
                foreach ($names as $k => $v) {
                    $queryParams[$v] = $matches[$k + 1];
                }
                // 替换路由中的变量
                $fragments   = explode('/', $route[0]);
                $fragments[] = $route[1];
                foreach ($fragments as $k => $v) {
                    preg_match('/{([\w-]+)}/i', $v, $matches);
                    if (!empty($matches)) {
                        list($fname) = $matches;
                        if (isset($queryParams[$fname])) {
                            $fragments[$k] = $queryParams[$fname];
                        }
                    }
                }
                // 记录参数
                $shortAction = array_pop($fragments);
                $shortClass  = implode('\\', $fragments);
                $result[]    = [[$shortClass, $shortAction, 'middleware' => isset($route['middleware']) ? $route['middleware'] : []], $queryParams];
            }
        }
        return $result;
    }
}
