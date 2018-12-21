<?php

$base = include "http_base.php";

// Apache/PHP-FPM 传统环境下运行的 HTTP 服务配置（传统模式）
return array_replace_recursive($base, [
    // 组件配置
    'components' => [
        // 请求
        'request' => [
            'class' => 'Mix\Http\Compatible\Request',
        ],

        'response' => [
            // 类路径
            'class' => 'Mix\Http\Compatible\Response',
        ],

        // 错误
        'error' => [
            // 错误级别
            'level' => E_ALL,
        ],
    ],
]);
