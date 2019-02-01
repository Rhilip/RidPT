<?php
/**
 * rid-httpd 下运行的 HTTP 服务配置（常驻协程模式）
 */

$base = include "http_base.php";

return array_replace_recursive($base, [
    // 组件配置
    'components' => [

        'token' => [
            'saveHandler' => [
                // 类路径
                'class' => Rid\Redis\Coroutine\RedisConnection::class,
                // 连接池
                'connectionPool' => [
                    // 组件路径
                    'component' => 'token.connectionPool',
                ],
            ],
        ],

        // 连接池
        'token.connectionPool' => [
            // 类路径
            'class' => Rid\Pool\ConnectionPool::class,
            // 最小连接数
            'min' => 5,
            // 最大连接数
            'max' => 50,
        ],

        // Session
        'session' => [
            // 保存处理者
            'saveHandler' => [
                // 类路径
                'class' => Rid\Redis\Coroutine\RedisConnection::class,
                // 连接池
                'connectionPool' => [
                    // 组件路径
                    'component' => 'session.connectionPool',
                ],
            ],
        ],

        // 连接池
        'session.connectionPool' => [
            // 类路径
            'class' => Rid\Pool\ConnectionPool::class,
            // 最小连接数
            'min' => 5,
            // 最大连接数
            'max' => 50,
        ],

        // 数据库
        'pdo' => [
            // 类路径
            'class' => Rid\Database\Coroutine\PDOConnection::class,
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'pdo.connectionPool',
            ],
        ],

        // 连接池
        'pdo.connectionPool' => [
            // 类路径
            'class' => Rid\Pool\ConnectionPool::class,
            // 最小连接数
            'min' => 5,
            // 最大连接数
            'max' => 50,
        ],

        // redis
        'redis' => [
            // 类路径
            'class' => Rid\Redis\Coroutine\RedisConnection::class,
            // 连接池
            'connectionPool' => [
                // 组件路径
                'component' => 'redis.connectionPool',
            ]
        ],

        // 连接池
        'redis.connectionPool' => [
            // 类路径
            'class' => mix\pool\ConnectionPool::class,
            // 最小连接数
            'min' => 5,
            // 最大连接数
            'max' => 50,
        ],

    ],
]);
