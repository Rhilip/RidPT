<?php

namespace Rid\Http;

use Rid\Helpers\ContainerHelper;

/**
 * Controller类
 */
class Controller
{
    public function render($name, $data = [])
    {
        return ContainerHelper::getContainer()->get('view')->render($name, $data);
    }
}
