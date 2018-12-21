<?php

namespace Mix\Http;

use Mix\Base\BaseObject;
use Mix\Helpers\XmlHelper;

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
