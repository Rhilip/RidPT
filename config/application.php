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

    // 基础路径
    'basePath' => dirname(__DIR__),

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

        // 数据库
        'pdo' => [
            // 类路径
            'class' => Rid\Database\Persistent\PDOConnection::class,
            // 数据源格式
            'dsn' => env('DATABASE_DSN'),
            // 数据库用户名
            'username' => env('DATABASE_USERNAME'),
            // 数据库密码
            'password' => env('DATABASE_PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],

        // Session
        'session' => [
            // 类路径
            'class' => Rid\Http\Session::class,
            // 保存的Key前缀
            'saveKeyPrefix' => 'Session:',
            // 生存时间
            'maxLifetime' => 7200,
            // session键名
            'name' => 'session_id',
            // 过期时间
            'cookieExpires' => 0,
            // 有效的服务器路径
            'cookiePath' => '/',
            // 有效域名/子域名
            'cookieDomain' => '',
            // 仅通过安全的 HTTPS 连接传给客户端
            'cookieSecure' => false,
            // 仅可通过 HTTP 协议访问
            'cookieHttpOnly' => false,
        ],

        'config' => [
            'class' => Rid\Component\Config::class,
        ],

        'site' => [
            'class' => App\Components\Site::class
        ],

        'auth' => [
            'class' => App\Components\Auth::class
        ],
    ],

    // 类库配置
    'libraries' => [
    ],
];
