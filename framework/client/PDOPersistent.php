<?php

namespace mix\client;

/**
 * PdoPersistent组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PDOPersistent extends BasePDOPersistent
{

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

}
