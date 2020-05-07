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
        // 路由
        'route' => [
            // 类路径
            'class' => Rid\Http\Route::class,
            // 默认变量规则
            'defaultPattern' => '[\w-]+',
            // 路由变量规则
            'patterns' => [
                'id' => '\d+'
            ],
            // 路由规则
            'rules' => [
                'GET tracker/{tracker_action}' => ['tracker', 'index'],
                'GET captcha' => ['captcha', 'index'],
                'GET maintenance' => ['maintenance', 'index'],

                // API version 1
                'api/v1/{controller}/{action}' => ['Api/v1/{controller}', '{action}', 'middleware' => [
                    App\Middleware\ApiMiddleware::class,
                    App\Middleware\AuthMiddleware::class
                ]],

                // Web view
                '{controller}/{action}' => ['{controller}', '{action}', 'middleware' => [
                    App\Middleware\AuthMiddleware::class
                ]],
            ],
        ],

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

        'site' => [
            'class' => App\Components\Site::class
        ],

        'auth' => [
            'class' => App\Components\Auth::class
        ],
    ],
];
