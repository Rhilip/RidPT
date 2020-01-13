<?php

namespace Rid\Database;

/**
 * Pdo组件
 */
class PDOConnection extends BasePDOConnection
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
