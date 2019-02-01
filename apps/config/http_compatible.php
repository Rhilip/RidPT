<?php
/**
 * Apache/PHP-FPM 传统环境下运行的 HTTP 服务配置（传统模式）
 */

$base = include "http_base.php";

return array_replace_recursive($base, [
    // 组件配置
    'components' => [
        'request' => [
            'class' => Rid\Http\Compatible\Request::class,
        ],

        'response' => [
            'class' => Rid\Http\Compatible\Response::class,
        ],

        'error' => [
            'level' => E_ALL,
        ],
    ],
]);
