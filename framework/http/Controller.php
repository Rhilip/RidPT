<?php

namespace Mix\Http;

use Mix\Base\BaseObject;

/**
 * Controller类
 * @author 刘健 <coder.liu@qq.com>
 */
class Controller extends BaseObject
{
    public function render($name, $data = [])
    {
        return (new View())->render($name, $data);
    }
}
