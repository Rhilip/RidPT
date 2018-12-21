<?php

namespace Mix\Http;

use Mix\Base\BaseObject;
use Mix\Helpers\JsonHelper;

/**
 * JSONP 类
 * @author 刘健 <coder.liu@qq.com>
 */
class Jsonp extends BaseObject
{

    // callback键名
    public $name = 'callback';

    // 编码
    public function encode($data)
    {
        // 不转义中文、斜杠
        $jsonString = JsonHelper::encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $callback   = \Mix::app()->request->get($this->name);
        if (is_null($callback)) {
            return $jsonString;
        }
        return $callback . '(' . $jsonString . ')';
    }

}
