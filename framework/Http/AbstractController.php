<?php

namespace Rid\Http;

use Rid\Base\AbstractObject;

/**
 * Controller类
 */
abstract class AbstractController extends AbstractObject
{
    public function render($name, $data = [])
    {
        return container()->get('view')->render($name, $data);
    }
}
