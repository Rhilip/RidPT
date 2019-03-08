<?php

namespace Rid\Http;

use Rid\Base\BaseObject;

/**
 * Controller类
 */
class Controller extends BaseObject
{

    protected $start_time;

    public function onInitialize()
    {
        $this->start_time = microtime(true);
    }

    public function render($name, $data = [])
    {
        $data['cost_time'] = microtime(true) - $this->start_time;
        if (env('APP_DEBUG'))
            $data['css_tag'] = time();
        return (new View())->render($name, $data);
    }
}
