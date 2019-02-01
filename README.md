<p align="center">
<img src="https://github.com/Rhilip/RidPT/raw/master/docs/RidPT.png" width='256px'><br/>
A Private Torrent Project framework.<br/>
<a href="https://github.com/Rhilip/RidPT/releases" title="GitHub Releases"><img src="https://img.shields.io/github/release/Rhilip/RidPT.svg"></a>
<img src="https://img.shields.io/badge/used-Swoole-blue.svg">
<a href="https://github.com/Rhilip/RidPT/LICENSE" title="GitHub license"><img src="https://img.shields.io/github/license/Rhilip/RidPT.svg"></a>
</p>

--------------------------------------

## Demo Site

Demo Url: http://ridpt.rhilip.info/

Test Account Information:
 - Username : `Admin`
 - Password : `123456`

## Installation Guide

1. We test our RidPT project on this environment :

    - Nginx
    - MySQL 5.7.22 (With InnoDB Storage Engine)
    - PHP 7.2.14 (With other extension which your can see in `composer.json`)
    - Swoole 4.2.12
    - Redis 5.0.3 Stable
    - Bower 1.8.4 (A package manager for the web)
 
2. After you prepare those base environment well

    - run below command to clone this repo or you can download from our release.
         ```bash
        git clone https://github.com/Rhilip/RidPT.git /home/wwwroot/your.domain.com
        cd /home/wwwroot/your.domain.com/
        composer install
        ```
    - Use `composer install` to install PHP dependency.
    - Use `bower install` to install our web dependency (like js,css,font)
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

5. Run Test by `php bin/rid-httpd service start -u` , And Congratulation If you see those output **without error throwout**.

    ```bash
    root@ridpt:/data/wwwroot/ridpt.rhilip.info# php bin/rid-httpd service start

    ────────────────────────────────────────────────────────────────────────────
    ─████████████████───██████████─████████████───██████████████─██████████████─
    ─██░░░░░░░░░░░░██───██░░░░░░██─██░░░░░░░░████─██░░░░░░░░░░██─██░░░░░░░░░░██─
    ─██░░████████░░██───████░░████─██░░████░░░░██─██░░██████░░██─██████░░██████─
    ─██░░██────██░░██─────██░░██───██░░██──██░░██─██░░██──██░░██─────██░░██─────
    ─██░░████████░░██─────██░░██───██░░██──██░░██─██░░██████░░██─────██░░██─────
    ─██░░░░░░░░░░░░██─────██░░██───██░░██──██░░██─██░░░░░░░░░░██─────██░░██─────
    ─██░░██████░░████─────██░░██───██░░██──██░░██─██░░██████████─────██░░██─────
    ─██░░██──██░░██───────██░░██───██░░██──██░░██─██░░██─────────────██░░██─────
    ─██░░██──██░░██████─████░░████─██░░████░░░░██─██░░██─────────────██░░██─────
    ─██░░██──██░░░░░░██─██░░░░░░██─██░░░░░░░░████─██░░██─────────────██░░██─────
    ─██████──██████████─██████████─████████████───██████─────────────██████─────
    ────────────────────────────────────────────────────────────────────────────

    Server      Name:      rid-httpd
    System      Name:      linux
    Framework   Version:   v0.1.2-alpha
    PHP         Version:   7.2.14
    Swoole      Version:   4.2.12
    Listen      Addr:      127.0.0.1
    Listen      Port:      9501
    Reactor     Num:       8
    Worker      Num:       8
    Hot         Update:    disabled
    Coroutine   Mode:      disabled
    Config      File:      /data/wwwroot/ridpt.rhilip.info/apps/config/http_permanent.php

    ```

6. Then you can safely add Nginx reserve proxy config like `migration/nginx.conf`.And Notice : 
If your service is behind the CDN like Cloudflare, You must follow [How do I restore original visitor IP with Nginx?](https://support.cloudflare.com/hc/en-us/articles/200170706-How-do-I-restore-original-visitor-IP-with-Nginx)
So that tracker can record the peer's ip address.

7. Use the default `php mix-httpd service start -d` to let *RidPT* RUN in the background. Or you can use other daemon work like:
    - Systemctl: [ridpt.service](migration/ridpt.service)

## Basie Environment in `.env`

### Section `APP`

> Document: None

| Key | Type | Note |
|:--|:--:|:--|
| ~~APP_ENV~~ | ENUM('local','staging') | Not use now. |
| APP_DEBUG | Bool | Turn on or off the debug model. |
| ~~APP_SECRET~~ | String | Not use now. |

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

### Section `Swiftmailer`

> Document: [Documentation – Swift Mailer](https://swiftmailer.symfony.com/docs/introduction.html#basic-usage)

| Key | Type | Note |
|:--|:--:|:--|
| MAILER_HOST | String | The mailer host. |
| MAILER_PORT | Int | The port to connect to. |
| MAILER_ENCRYPTION | Enum('tls','ssl') | The encryption type. |
| MAILER_USERNAME | String | The username to authenticate with. |
| MAILER_PASSWORD | String | The password to authenticate with. |
| MAILER_FROM | String | The from address of this message. |
| ~~MAILER_FROM_NICKNAME~~ | String | Not use now. |

## Copyright

### This project

Apache License 2.0

### our dependency

 - [MixPHP](https://github.com/mix-php/mix-framework) : Apache License 2.0
 - [Symfony Components](https://symfony.com/) , Like [twig](https://twig.symfony.com), [swiftmailer](https://swiftmailer.symfony.com) : MIT
 - [sandfoxme/bencode](https://github.com/sandfoxme/bencode) : MIT
 - [RobThree/TwoFactorAuth](https://github.com/RobThree/TwoFactorAuth) : MIT

