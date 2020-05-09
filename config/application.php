<?php
/**
 * rid-httpd 下运行的 HTTP 服务配置（常驻同步模式）
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/26
 * Time: 22:40
 */

return [
    // 控制器命名空间
    'controllerNamespace' => 'App\Controllers',

    // 全局中间件
    'middleware' => [
        App\Middleware\IpBanMiddleware::class
    ],

    // 组件配置
    'components' => [
        // 请求
        'request' => [
            'class' => Rid\Http\Message\Request::class,
            'trustedProxies' => ['127.0.0.1', '::1'],
            //'trustedHosts' => -1,
        ],

        // 响应
        'response' => [
            'class' => Rid\Http\Message\Response::class,
        ],

        // 错误
        'error' => [
            'class' => Rid\Http\Error::class
        ],
    ],
];
