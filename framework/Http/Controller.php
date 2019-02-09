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
        $data['db_count'] = count(app()->pdo->getExecuteData());
        if (env('APP_DEBUG'))
            $data['css_tag'] = time();
        $data['redis_count'] = array_sum(app()->redis->getCalledData());
        return (new View())->render($name, $data);
    }
}
