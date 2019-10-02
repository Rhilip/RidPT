<?php

namespace Rid\Base;

/**
 *
 * @property \Rid\Component\Log $log
 * @property \Rid\Database\PDOConnection $pdo
 * @property \Rid\Redis\RedisConnection $redis
 * @property \Rid\Component\Config $config
 * @property \Rid\Component\I18n $i18n
 * @property \Rid\Component\View $view
 * @property \App\Components\Site $site
 */
class Application extends BaseObject
{
    // 初始化回调
    public $initialize = [];
    // 基础路径
    public $basePath = '';
    // 组件配置
    public $components = [];
    // 类库配置
    public $libraries = [];
    // 组件容器
    protected $_components;
    // 组件命名空间
    protected $_componentPrefix;

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        // 快捷引用
        \Rid::setApp($this);
        // 执行初始化回调
        foreach ($this->initialize as $callback) {
            call_user_func($callback);
        }
    }

    // 设置组件命名空间
    public function setComponentPrefix($prefix)
    {
        $this->_componentPrefix = $prefix;
    }

    // 装载组件
    public function loadComponent($name, $return = false)
    {
        // 已加载
        if (!$return && isset($this->_components[$name])) {
            return;
        }
        // 未注册
        if (!isset($this->components[$name])) {
            throw new \Rid\Exceptions\ComponentException("组件不存在：{$name}");
        }
        // 使用配置创建新对象
        $object = \Rid::createObject($this->components[$name]);
        // 组件效验
        if (!($object instanceof ComponentInterface)) {
            throw new \Rid\Exceptions\ComponentException("不是组件类型：{$this->components[$name]['class']}");
        }
        if ($return) {
            return $object;
        }
        // 装入容器
        $this->_components[$name] = $object;
    }

    // 获取配置
    public function env($name)
    {
        $message = "Environment key {$name} does not exist.";
        // 处理带前缀的名称
        preg_match('/(\[[\w.]+\])/', $name, $matches);
        $subname = array_pop($matches);
        $name = str_replace($subname, str_replace('.', '|', $subname), $name);
        $fragments = explode('.', $name);
        foreach ($fragments as $key => $value) {
            if (strpos($value, '[') !== false) {
                $fragments[$key] = str_replace(['[', ']'], '', $value);
                $fragments[$key] = str_replace('|', '.', $fragments[$key]);
            }
        }
        // 判断一级配置是否存在
        $first = array_shift($fragments);
        if (!isset($this->$first)) {
            throw new \Rid\Exceptions\ConfigException($message);
        }
        // 判断其他配置是否存在
        $current = $this->$first;
        foreach ($fragments as $key) {
            if (!isset($current[$key])) {
                throw new \Rid\Exceptions\ConfigException($message);
            }
            $current = $current[$key];
        }
        return $current;
    }

    // 获取配置目录路径
    public function getConfigPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    // 获取运行目录路径
    public function getRuntimePath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'var';
    }
}
