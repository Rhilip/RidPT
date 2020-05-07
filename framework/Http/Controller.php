<?php

namespace Rid\Http;

use DI\Container;

/**
 * Controller类
 */
class Controller
{

    protected Container $container;

    public function __construct(Container $container)
    {
    }


    public function render($name, $data = [])
    {
        return $this->container->get('view')->render($name, $data);
    }
}
