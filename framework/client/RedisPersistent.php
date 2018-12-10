<?php

namespace mix\client;

/**
 * redis长连接组件
 * @author 刘健 <coder.liu@qq.com>
 */
class RedisPersistent extends BaseRedisPersistent
{

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

}
