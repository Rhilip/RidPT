<?php

namespace Rid\Http;

use Rid\Base\AbstractObject;

/**
 * Controller类
 */
class Controller extends AbstractObject
{
    public function render($name, $data = [])
    {
        return $this->container->get('view')->render($name, $data);
    }
}
