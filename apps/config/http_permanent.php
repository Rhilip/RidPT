<?php
/**
 * rid-httpd 下运行的 HTTP 服务配置（常驻同步模式）
 */

$base = include "http_base.php";

return array_replace_recursive($base, [

    'components' => [

        'pdo' => [
            'class' => Rid\Database\Persistent\PDOConnection::class,
        ],

        'redis' => [
            'class' => Rid\Redis\Persistent\RedisConnection::class,
        ],

        'config' => [
            'class'   => Rid\Config\ConfigBySwoole::class,
        ]
    ],
]);
