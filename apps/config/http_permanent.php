<?php
/**
 * mix-httpd 下运行的 HTTP 服务配置（常驻同步模式）
 */

$base = include "http_base.php";

return array_replace_recursive($base, [

    'components' => [

        'pdo' => [
            'class' => Mix\Database\Persistent\PDOConnection::class,
        ],

        'redis' => [
            'class' => Mix\Redis\Persistent\RedisConnection::class,
        ],
    ],
]);
