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
    'path.config' => DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'config'),
    'path.public' => DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'public'),
    'path.templates' => DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'templates'),

    'path.runtime' => DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'var'),
    'path.runtime.logs' => DI\string('{path.runtime}' . DIRECTORY_SEPARATOR . 'logs'),

    'path.storage' => DI\string('{path.root}' . DIRECTORY_SEPARATOR . 'storage'),
    'path.storage.torrents' => DI\string('{path.storage}' . DIRECTORY_SEPARATOR . 'torrents'),
    'path.storage.subs' => DI\string('{path.storage}' . DIRECTORY_SEPARATOR . 'subs'),

    // 定义组件
    'view' => DI\autowire(\Rid\Component\View::class),

    'mailer' => DI\autowire(\App\Components\Mailer::class)
        ->property('from', DI\env('MAILER_FROM'))
        ->property('fromname', DI\env('MAILER_FROMNAME')),

    'logger' => DI\autowire(Monolog\Logger::class)
        ->constructor(PROJECT_NAME)
        ->method('pushHandler', DI\get(\Monolog\Handler\RotatingFileHandler::class)),

    // 定义组件依赖
    \League\Plates\Engine::class => \DI\create()
        ->constructor(DI\get('path.templates'))
        ->method('loadExtension', DI\create(Rid\View\Conversion::class)),

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
];
