<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

return [
    // 定义路径
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

    // 定义组件
    'redis' => \DI\autowire(\Rid\Redis\BaseRedisConnection::class)
        ->property('host', \DI\env('REDIS_HOST'))
        ->property('port', \DI\env('REDIS_PORT'))
        ->property('password', \DI\env('REDIS_PASSWORD'))
        ->property('database', \DI\env('REDIS_DATABASE'))
        ->property('options', [
            \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,
            \Redis::OPT_PREFIX => ''
        ])
        ->method('connectRedis'),

    'view' => \DI\autowire(\Rid\Component\View::class),

    'mailer' => \DI\autowire(\App\Components\Mailer::class)
        ->property('from', \DI\env('MAILER_FROM'))
        ->property('fromname', \DI\env('MAILER_FROMNAME')),

    'logger' => \DI\autowire(Monolog\Logger::class)
        ->constructor(PROJECT_NAME)
        ->method('pushHandler', \DI\get(\Monolog\Handler\RotatingFileHandler::class)),

    'i18n' => \DI\autowire(\Rid\Component\I18n::class)
        ->property('allowedLangSet', ['en', 'zh-CN'])
        ->property('forcedLang', null),

    // 定义对象
    'captcha' => \DI\get(\Rid\Libraries\Captcha::class),
    'emitter' => \DI\get(\League\Event\Emitter::class),

    // 定义组件依赖
    \League\Event\Emitter::class => \DI\create(),  // FIXME add listener

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
        ->property('yRand', [5, 15])
];
