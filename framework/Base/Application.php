<?php

namespace Rid\Base;

use DI\Container;

class Application
{
    // Application配置
    public array $config = [];

    protected Context $context;
    protected ?Container $container;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->container = $this->buildContainer();
        $this->context = new Context();

        // 执行初始化回调
        foreach ($this->config['initialize'] ?? [] as $callback) {
            call_user_func($callback, $this);
        }
    }

    protected function buildContainer()
    {
        $builder = new \DI\ContainerBuilder();
        $builder->addDefinitions($this->config['components']);
        return $builder->build();
    }

    /**
     * @return Container|null
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
