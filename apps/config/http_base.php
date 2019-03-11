<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/26
 * Time: 22:40
 */

return [

    // 基础路径
    'basePath'         => dirname(__DIR__),

    // 控制器命名空间
    'controllerNamespace' => 'apps\controllers',

    // 全局中间件
    'middleware'          => [
        apps\middleware\IpBanMiddleware::class,
        apps\middleware\DebugMiddleware::class
    ],

    // 组件配置
    'components'          => [
        // 路由
        'route'    => [
            // 类路径
            'class'          => Rid\Http\Route::class,
            // 默认变量规则
            'defaultPattern' => '[\w-]+',
            // 路由变量规则
            'patterns'       => [
                'id' => '\d+'
            ],
            // 路由规则
            'rules'          => [
                'GET tracker/{tracker_action}' => ['tracker','index'],
                'GET captcha' => ['captcha', 'index'],
                'GET rss' => ['rss', 'index','middleware' => [
                    apps\middleware\AuthByPasskeyMiddleware::class
                ]],
                'api/v1/{controller}/{action}' => ['api/{controller}', '{action}', 'middleware' => [
                    apps\middleware\ApiMiddleware::class
                ]],
                '{controller}/{action}' => ['{controller}', '{action}', 'middleware' => [
                    apps\middleware\AuthByCookiesMiddleware::class
                ]],
            ],
        ],

        // 请求
        'request'  => [
            'class' => Rid\Http\Request::class,
        ],

        // 响应
        'response' => [
            // 类路径
            'class'         => Rid\Http\Response::class,
            // 默认输出格式
            'defaultFormat' => Rid\Http\Response::FORMAT_HTML,
        ],

        // 错误
        'error'    => [
            'class'  => Rid\Http\Error::class,
            'format' => Rid\Http\Error::FORMAT_HTML,
        ],

        // 日志
        'log' => [
            'class'       => Rid\Log\Log::class,  // 类路径
            'dir'         => 'logs',  // 日志目录
            'rotate'      => Rid\Log\Log::ROTATE_DAY,  // 日志轮转类型
            'maxFileSize' => 0,  // 最大文件尺寸
        ],

        // Token
        'token'    => [
            // 类路径
            'class'         => Rid\Http\Token::class,
            // 保存处理者
            'saveHandler'   => [
                // 类路径
                'class'    => Rid\Redis\RedisConnection::class,
                // 主机
                'host'     => env('REDIS_HOST'),
                // 端口
                'port'     => env('REDIS_PORT'),
                // 数据库
                'database' => env('REDIS_DATABASE'),
                // 密码
                'password' => env('REDIS_PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix' => 'TOKEN:',
            // 有效期
            'expiresIn'     => 604800,
            // token键名
            'name'          => 'access_token',
        ],

        // Session
        'session'  => [
            // 类路径
            'class'          => Rid\Http\Session::class,
            // 保存处理者
            'saveHandler'    => [
                // 类路径
                'class'    => Rid\Redis\RedisConnection::class,
                // 主机
                'host'     => env('REDIS_HOST'),
                // 端口
                'port'     => env('REDIS_PORT'),
                // 数据库
                'database' => env('REDIS_DATABASE'),
                // 密码
                'password' => env('REDIS_PASSWORD'),
            ],
            // 保存的Key前缀
            'saveKeyPrefix'  => 'SESSION:',
            // 生存时间
            'maxLifetime'    => 7200,
            // session键名
            'name'           => 'session_id',
            // 过期时间
            'cookieExpires'  => 0,
            // 有效的服务器路径
            'cookiePath'     => '/',
            // 有效域名/子域名
            'cookieDomain'   => '',
            // 仅通过安全的 HTTPS 连接传给客户端
            'cookieSecure'   => false,
            // 仅可通过 HTTP 协议访问
            'cookieHttpOnly' => false,
        ],

        // Cookie
        'cookie'   => [
            // 类路径
            'class'    => Rid\Http\Cookie::class,
            // 过期时间
            'expires'  => 31536000,
            // 有效的服务器路径
            'path'     => '/',
            // 有效域名/子域名
            'domain'   => '',
            // 仅通过安全的 HTTPS 连接传给客户端
            'secure'   => false,
            // 仅可通过 HTTP 协议访问
            'httpOnly' => false,
        ],

        // 数据库
        'pdo'      => [
            // 类路径
            'class'         => Rid\Database\PDOConnection::class,
            // 数据源格式
            'dsn'           => env('DATABASE_DSN'),
            // 数据库用户名
            'username'      => env('DATABASE_USERNAME'),
            // 数据库密码
            'password'      => env('DATABASE_PASSWORD'),
            // 驱动连接选项: http://php.net/manual/zh/pdo.setattribute.php
            'driverOptions' => [
                // 设置默认的提取模式: \PDO::FETCH_OBJ | \PDO::FETCH_ASSOC
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ],
        ],

        // redis
        'redis'    => [
            // 类路径
            'class'    => Rid\Redis\RedisConnection::class,
            // 主机
            'host'     => env('REDIS_HOST'),
            // 端口
            'port'     => env('REDIS_PORT'),
            // 数据库
            'database' => env('REDIS_DATABASE'),
            // 密码
            'password' => env('REDIS_PASSWORD'),
            'driverOptions' => [
                \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            ]
        ],

        'config' => [
            'class'   => Rid\Config\DynamicConfig::class,
        ],

        'user' => [
            'class' => Rid\User\User::class,
        ]
    ],

    // 类库配置
    'libraries'           => [
        'swiftmailer' => [
            'class'   => Rid\Libraries\Mailer::class,
            'host'    => env('MAILER_HOST'),
            'port'    => env('MAILER_PORT'),
            'encryption' => env('MAILER_ENCRYPTION'),
            'username'=> env('MAILER_USERNAME'),
            'password'=> env('MAILER_PASSWORD'),
            'from'    => env('MAILER_FROM'),
            'nikename'=> env('MAILER_FROM_NICKNAME'),
        ],
    ],
];
