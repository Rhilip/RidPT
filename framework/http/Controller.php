<?php

namespace mix\http;

use apps\common\facades\Config;

use mix\base\BaseObject;

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
