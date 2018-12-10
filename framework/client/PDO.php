<?php

namespace mix\client;

/**
 * Pdo组件
 * @author 刘健 <coder.liu@qq.com>
 */
class PDO extends BasePDO
{

    // 请求后置事件
    public function onRequestAfter()
    {
        parent::onRequestAfter();
        // 关闭连接
        $this->disconnect();
    }

    // 析构事件
    public function onDestruct()
    {
        parent::onDestruct();
        // 关闭连接
        $this->disconnect();
    }

}
