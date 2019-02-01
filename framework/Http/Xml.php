<?php

namespace Rid\Http;

use Rid\Base\BaseObject;
use Rid\Helpers\XmlHelper;

/**
 * Xml类
 */
class Xml extends BaseObject
{

    // 编码
    public function encode($data)
    {
        return XmlHelper::encode($data);
    }

}
