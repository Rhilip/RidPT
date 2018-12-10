<?php

namespace mix\http;

use mix\base\BaseObject;
use mix\helpers\XmlHelper;

/**
 * Xml类
 * @author 刘健 <coder.liu@qq.com>
 */
class Xml extends BaseObject
{

    // 编码
    public function encode($data)
    {
        return XmlHelper::encode($data);
    }

}
