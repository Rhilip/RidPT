<?php

$base = include "http_base.php";

// mix-httpd 下运行的 HTTP 服务配置（常驻同步模式）
return array_replace_recursive($base, [
    // 组件配置
    'components' => [
        // 数据库
        'pdo' => [
            // 类路径
            'class' => 'mix\client\PDOPersistent',
        ],

        // redis
        'redis' => [
            // 类路径
            'class' => 'mix\client\RedisPersistent',
        ],
    ],
]);
