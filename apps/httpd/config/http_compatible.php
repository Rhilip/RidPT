<?php

$base = include "http_base.php";

// Apache/PHP-FPM 传统环境下运行的 HTTP 服务配置（传统模式）
return array_replace_recursive($base, [
    // 组件配置
    'components' => [
        // 请求
        'request' => [
            'class' => 'mix\http\compatible\Request',
        ],

        'response' => [
            // 类路径
            'class' => 'mix\http\compatible\Response',
        ],

        // 错误
        'error' => [
            // 错误级别
            'level' => E_ALL,
        ],
    ],
]);
