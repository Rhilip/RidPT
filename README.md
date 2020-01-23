<p align="center">
<img src="https://github.com/Rhilip/RidPT/raw/master/public/static/pic/logo.png" width='256px'><br/>
Another Private Torrent framework Project. <b>On Heavily Development now !!!</b><br/>
</p>

[![GitHub Releases](https://img.shields.io/github/release/Rhilip/RidPT.svg)](https://github.com/Rhilip/RidPT/releases)
[![used-Swoole-blue](https://img.shields.io/badge/used-Swoole-blue.svg)](https://www.swoole.com/)
[![GitHub license](https://img.shields.io/github/license/Rhilip/RidPT.svg)](https://github.com/Rhilip/RidPT/blob/master/LICENSE)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRhilip%2FRidPT.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2FRhilip%2FRidPT?ref=badge_shield)
[![Telegram Group](https://img.shields.io/badge/telegram-Group-blue.svg?logo=telegram)](https://t.me/ridpt)

--------------------------------------

## Demo Site

Demo Url: https://ridpt.top/

Test Account Information:
 - Username : `Admin`
 - Password : `123456`

## Installation Guide

1. We test our RidPT project on this environment :

    - Nginx 1.14.2
    - MySQL 8.0.17 **(At least)** 
    - PHP 7.4.1 (With other extension which your can see in `composer.json`)
    - Swoole 4.2.12
    - Redis 5.0.3 Stable
    - Bower 1.8.4 (A package manager for the web)
    - *Suggest*
      - [phpmyadmin/phpmyadmin](<https://github.com/phpmyadmin/phpmyadmin>)
      - [erikdubbelboer/phpRedisAdmin](<https://github.com/erikdubbelboer/phpRedisAdmin>)

2. After you prepare those base environment well

    - run below command to clone this repo or you can download from our release.
         ```bash
        git clone https://github.com/Rhilip/RidPT.git /home/wwwroot/your.domain.com
        cd /home/wwwroot/your.domain.com/
        ```
    - Use `composer install` to install PHP dependency.
    - Use `bower install` to install our front-end dependency (like js,css,font)
    - If you don't run RidPT apps in `root` user, you should give enough permission to `apps/{runtime,private}`.

3. Import Database Structure from our `migration/ridpt.sql`, and **disable Mysql strict mode `NO_ZERO_IN_DATE` and `NO_ZERO_DATE`**.

    ```bash
    mysql -u root -p < migration/ridpt.sql
    ```
 
4. Then set your RidPT Project config (about APP, DATABASE, Redis, Mailer) in `.env`. The other config about site your can 
edit in Admin Panel.

    ```bash
    cp .env.example .env
    vi .env
    ```

5. Run Test by `php bin/console server start -u` , And Congratulation If you see those output **without error throwout**.

    ```bash
    root@Ubuntu-iso-DND:/data/wwwroot/ridpt.top# php bin/console server start -u
    2019-09-17 22:24:29 ───────────────────────────────────────
    2019-09-17 22:24:29 Server      Name:      RidPT
    2019-09-17 22:24:29 System      Name:      Linux
    2019-09-17 22:24:29 Framework   Version:   v0.1.5-alpha
    2019-09-17 22:24:29 PHP         Version:   7.3.7
    2019-09-17 22:24:29 Swoole      Version:   4.4.0
    2019-09-17 22:24:29 Listen      Addr:      127.0.0.1
    2019-09-17 22:24:29 Listen      Port:      9501
    2019-09-17 22:24:29 Reactor     Num:       1
    2019-09-17 22:24:29 Worker      Num:       5
    2019-09-17 22:24:29 Hot         Update:    disabled
    2019-09-17 22:24:29 Coroutine   Mode:      disabled
    2019-09-17 22:24:29 Config      File:      /data/wwwroot/ridpt.top/config/http_base.php
    2019-09-17 22:24:29 ───────────────────────────────────────
    ```

6. Then you can safely add Nginx reserve proxy config like `migration/nginx.conf`.And Notice : 
If your service is behind the CDN like Cloudflare, You must follow [How do I restore original visitor IP with Nginx?](https://support.cloudflare.com/hc/en-us/articles/200170706-How-do-I-restore-original-visitor-IP-with-Nginx)
So that tracker can record the peer's ip address.

7. Use the default `php bin/console server start -d` to let *RidPT* RUN in the background. Or you can use other daemon work like:
    - Systemctl: [ridpt.service](migration/ridpt.service)

## Basie Environment Setting in `.env`

> Notice: Any change in file `.env` require the restart of Application to Make it effective 

### Section `APP`

> Document: None

| Key | Type | Note |
|:--|:--:|:--|
| ~~APP_ENV~~ | ENUM('local','staging') | Not use now. |
| APP_DEBUG | Bool | Turn on or off the debug model,Turn if off after success deploy ! |
| APP_SECRET_KEY | String | The key used to encrypt and decrypt some data.**(Change it before deploy and don't Change it after Deploy)** |
| APP_SECRET_IV | String | A non-NULL Initialization Vector.**(Change it before deploy and don't Change it after Deploy)** |

### Section `Database`

> Document: [PHP: PDO::__construct - Manual](https://secure.php.net/manual/en/pdo.construct.php)

| Key | Type | Note |
|:--|:--:|:--|
| DATABASE_DSN | DSN | The Data Source Name, or DSN, contains the information required to connect to the database. |
| DATABASE_USERNAME | String | The user name for the DSN string. |
| DATABASE_PASSWORD | String | The password for the DSN string. |

### Section `Redis`

> Document: [phpredis/phpredis](https://github.com/phpredis/phpredis#connection)

| Key | Type | Note |
|:--|:--:|:--|
| REDIS_HOST | String | A host, or the path to a unix domain socket. |
| REDIS_PORT | Int | Dy default 6379. |
| REDIS_DATABASE | Int | dbindex, the database number to switch to. |
| REDIS_PASSWORD | String | The password for the Redis server. |

### Section `Mailer`

> Document: [the PHPMailer wiki](https://github.com/PHPMailer/PHPMailer/wiki)

| Key | Type | Note |
|:--|:--:|:--|
| MAILER_DEBUG | Int | SMTP class debug output mode. |
| MAILER_HOST | String | SMTP hosts. |
| MAILER_PORT | Int | The default SMTP server port. |
| MAILER_ENCRYPTION | Enum('','tls','ssl') | What kind of encryption to use on the SMTP connection. |
| MAILER_USERNAME | String | SMTP username. |
| MAILER_PASSWORD | String | SMTP password. |
| MAILER_FROM | String | The from address of this email. |
| MAILER_FROMNAME | String | The from name of this email. |

## Dynamic Config

> Notice: Most Dynamic config note you can found in our config setting Page

// TODO

## Development Help

Some rule or Docs May help you when you rebuild this project,
Or you can join our chat group on Telegram -- [@ridpt](https://t.me/ridpt)

### FrontEnd

| Library | Docs |
|:--|:--|
| [Zui](https://github.com/easysoft/zui): an HTML5 front UI framework | <http://zui.sexy/> ( Chinese Version ) |
| [FortAwesome](https://github.com/FortAwesome/Font-Awesome): The iconic SVG, font, and CSS toolkit | <https://fontawesome.com/icons?d=gallery> |
| [flag-css](https://github.com/7kfpun/flag-css): CSS for SVG country flags respecting the original ratio. | <https://kfpun.com/flag-css/> |
| [zxcvbn](https://github.com/dropbox/zxcvbn): Low-Budget Password Strength Estimation | <https://lowe.github.io/tryzxcvbn/> |
| [bootstrap-validator](https://github.com/1000hz/bootstrap-validator): A user-friendly HTML5 form validation jQuery plugin for Bootstrap 3 | <http://1000hz.github.io/bootstrap-validator> |
| [jQuery.Textarea.Autoresize](https://github.com/AndrewDryga/jQuery.Textarea.Autoresize): Smart resizing for textareas using jQuery | <https://github.com/AndrewDryga/jQuery.Textarea.Autoresize> |

### Backend Library

| Library | Used As | Docs | 
|:--|:--:|:--|
| [MixPHP](https://github.com/mix-php/mix-framework/tree/v1) | Framework | <https://www.kancloud.cn/onanying/mixphp1/379324> ( Chinese Version ) |
| [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv) | phpdotenv | <https://github.com/vlucas/phpdotenv> |
| [adhocore/cli](https://github.com/adhocore/php-cli) | Console Application | <https://github.com/adhocore/php-cli> |
| [siriusphp/validation](https://github.com/siriusphp/validation) | Validator | <http://www.sirius.ro/php/sirius/validation/> |
| [league/plates](https://github.com/thephpleague/plates) | Template system | <http://platesphp.com/> |
| [firebase/php-jwt](https://github.com/firebase/php-jwt) | JWT | <https://github.com/firebase/php-jwt>, <https://jwt.io/> |

## Sponsor

![](https://meihezi.cache.ejcdn.com/images/common/logo.png) [MeiHeZi](https://www.meihezi.com) For Demo Site Host Server

## License
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2FRhilip%2FRidPT.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2FRhilip%2FRidPT?ref=badge_large)

