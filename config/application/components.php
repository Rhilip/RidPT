<?php
/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 *
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/26
 * Time: 22:40
 */

return [
    // 定义 应用状态
    'app.env' => \DI\env('APP_ENV'),
    'app.debug' => \DI\env('APP_DEBUG'),
    'app.secret_key' => \DI\env('APP_SECRET_KEY'),
    'app.secret_iv' => \DI\env('APP_SECRET_IV'),

    // 定义 路径
    'path.root' => RIDPT_ROOT,
    'path.config' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'config'),
    'path.public' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'public'),
    'path.templates' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'templates'),
    'path.translations' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'translations'),

    'path.runtime' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'var'),
    'path.runtime.logs' => \DI\string('{path.runtime}' . DIRECTORY_SEPARATOR . 'logs'),
    'path.runtime.translation' => \DI\string('{path.runtime}' . DIRECTORY_SEPARATOR . 'translation'),

    'path.storage' => \DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'storage'),
    'path.storage.torrents' => \DI\string('{path.storage}' . DIRECTORY_SEPARATOR . 'torrents'),
    'path.storage.subs' => \DI\string('{path.storage}' . DIRECTORY_SEPARATOR . 'subs'),

    // 定义组件快捷引用
    'request' => \DI\get(\Rid\Http\Message\Request::class),
    'response' => \DI\get(\Rid\Http\Message\Response::class),
    'logger' => \DI\get(Monolog\Logger::class),
    'mailer' => \DI\get(\App\Components\Mailer::class),
    'pdo' => \DI\get(\Rid\Database\Persistent\PDOConnection::class),
    'redis' => \DI\get(\Rid\Redis\BaseRedisConnection::class),
    'session' => \DI\get(\Rid\Http\Session::class),
    'i18n' => \DI\get(\Rid\Component\I18n::class),
    'config' => \DI\get(\Rid\Component\Config::class),
    'site' => \DI\get(\App\Components\Site::class),
    'auth' => \DI\get(\App\Components\Auth::class),
    'view' => \DI\get(\Rid\Component\View::class),
    'validator' => \DI\get(Symfony\Component\Validator\Validator\ValidatorInterface::class),

    // 定义对象快捷引用
    'emitter' => \DI\get(\League\Event\Emitter::class),
    'runtime' => \DI\get(\Rid\Component\Context::class),

    // 定义组件实体
    \Rid\Http\Message\Request::class => \DI\autowire()
        ->method('setTrustedProxies', ['127.0.0.1', '::1'], \Rid\Http\Message\Request::HEADER_X_FORWARDED_ALL),

    \Rid\Http\Message\Response::class => \DI\autowire(),

    Monolog\Logger::class => \DI\autowire()
        ->constructor(PROJECT_NAME)
        ->method('pushHandler', \DI\get(\Monolog\Handler\RotatingFileHandler::class)),

    \App\Components\Mailer::class => \DI\autowire()
        ->property('from', \DI\env('MAILER_FROM'))
        ->property('fromname', \DI\env('MAILER_FROMNAME')),

    \Rid\Redis\BaseRedisConnection::class => \DI\autowire()
        ->property('host', \DI\env('REDIS_HOST'))
        ->property('port', \DI\env('REDIS_PORT'))
        ->property('password', \DI\env('REDIS_PASSWORD'))
        ->property('database', \DI\env('REDIS_DATABASE'))
        ->property('options', [
            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            \Redis::OPT_PREFIX => ''
        ])
        ->method('connectRedis'),

    \Rid\Database\Persistent\PDOConnection::class => \DI\autowire()
        ->property('dsn', \DI\env('DATABASE_DSN'))
        ->property('username', \DI\env('DATABASE_USERNAME'))
        ->property('password', \DI\env('DATABASE_PASSWORD'))
        ->property('options', [
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ]),

    \Rid\Http\Session::class => \DI\autowire()
        ->property('idLength', 26)  // SessionId长度

        // 服务器保存设置（暂只支持使用Redis保存）
        ->property('saveKeyPrefix', 'Session:')   // 保存的Key前缀
        ->property('maxLifetime', 7200)           // 生存时间

        // 用户侧Cookies设置
        ->property('cookieName', 'session_id') // session名
        ->property('cookieExpires', 0)         // 过期时间
        ->property('cookiePath', '/')          // 有效的服务器路径
        ->property('cookieDomain', '')         // 有效域名/子域名
        ->property('cookieSecure', false)      // 仅通过安全的 HTTPS 连接传给客户端
        ->property('cookieHttpOnly', false),   // 仅可通过 HTTP 协议访问

    Symfony\Component\Validator\Validator\ValidatorInterface::class => \DI\factory(function () {
        return \Symfony\Component\Validator\Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->getValidator();
    }),

    \Rid\Component\Config::class => \DI\autowire(),
    \App\Components\Site::class => \DI\autowire(),
    \App\Components\Auth::class => \DI\autowire(),
    \Rid\Component\View::class => \DI\autowire(),

    // 定义组件依赖
    \League\Event\Emitter::class => \DI\create()
        ->method('useListenerProvider', \DI\autowire(App\Event\Provider\RuntimeProvider::class)),

    \League\Plates\Engine::class => \DI\create()
        ->constructor(DI\get('path.templates'))
        ->method('loadExtension', DI\autowire(Rid\View\Conversion::class)),

    \Decoda\Decoda::class => \DI\create()
        ->constructor('', ['escapeHtml' => true], '')
        ->method('defaults')  // TODO add support of tag [mediainfo]
        ->method('setStorage', DI\autowire(\Decoda\Storage\RedisStorage::class)),

    \Monolog\Handler\RotatingFileHandler::class => DI\create()
        ->constructor(DI\string('{path.runtime.logs}' . DIRECTORY_SEPARATOR . 'ridpt.log'), 10),

    \PHPMailer\PHPMailer\PHPMailer::class => DI\create()
        ->constructor(DI\env('APP_DEBUG'))
        ->property('Host', DI\env('MAILER_HOST'))
        ->property('Port', DI\env('MAILER_PORT'))
        ->method('isSMTP')
        ->property('SMTPDebug', DI\env('MAILER_DEBUG'))
        ->property('SMTPSecure', DI\env('MAILER_ENCRYPTION'))
        ->property('SMTPAuth', true)
        ->property('Username', DI\env('MAILER_USERNAME'))
        ->property('Password', DI\env('MAILER_PASSWORD'))
        ->property('CharSet', \PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8),

    \Symfony\Component\Translation\Translator::class => \DI\create()
        ->constructor('en' /* fallbackLang */, null, DI\get('path.runtime.translation') /* cacheDir */)
        ->method('addLoader', 'json', DI\create(Symfony\Component\Translation\Loader\JsonFileLoader::class))
        ->method('addResource', 'json', DI\string('{path.translations}' . DIRECTORY_SEPARATOR . 'locale-en.json'), 'en')
        ->method('addResource', 'json', DI\string('{path.translations}' . DIRECTORY_SEPARATOR . 'locale-zh_CN.json'), 'zh_CN'),

    \Rid\Component\I18n::class => \DI\autowire()
        ->property('allowedLangSet', ['en', 'zh-CN'])
        ->property('forcedLang', null),

    // 定义对象实体
    \Rid\Libraries\Captcha::class => DI\create()
        ->property('width', 150)
        ->property('height', 40)
        ->property('wordSet', 'abcdefghjkmnpqrtuvwxy346789ABCDEFJHKMNPQRTUVWX')
        ->property('fontFile', DI\string('{path.public}' . DIRECTORY_SEPARATOR . 'static/fonts/Times New Roman.ttf'))
        ->property('fontSize', 20)
        ->property('wordNumber', 6)
        ->property('angleRand', [-20, 20])
        ->property('xSpacing', 0.82)
        ->property('yRand', [5, 15]),

    \Rid\Libraries\JWT::class => \DI\create()
        ->constructor(\DI\get('app.secret_key'), ['HS256']),

    \Rid\Libraries\Crypt::class => \DI\create()
        ->constructor(\DI\get('app.secret_key'), \DI\get('app.secret_iv'), 'AES-256-CBC')

];
