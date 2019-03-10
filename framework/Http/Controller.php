<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

/**
 * Controller类
 */
class Controller extends BaseObject
{

    protected $start_time;

    public function render($name, $data = [])
    {
        return (new View())->render($name, $data);
    }
}
