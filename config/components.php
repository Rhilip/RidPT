<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

return [


    'logger' => DI\create(Monolog\Logger::class)
        ->constructor(PROJECT_NAME)
        ->method('pushHandler', DI\get(\Monolog\Handler\RotatingFileHandler::class)),

    'mailer' => DI\autowire(\App\Libraries\Mailer::class)
        ->property('from', DI\env('MAILER_FROM'))
        ->property('fromname', DI\env('MAILER_FROMNAME')),


    \Monolog\Handler\RotatingFileHandler::class => DI\create()
        ->constructor(RIDPT_ROOT . '/var/logs/ridpt.log', 10),

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
