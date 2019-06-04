<p align="center">
<img src="https://github.com/Rhilip/RidPT/raw/master/apps/public/static/pic/logo.png" width='256px'><br/>
A Private Torrent framework Project.<br/>
<a href="https://github.com/Rhilip/RidPT/releases" title="GitHub Releases"><img src="https://img.shields.io/github/release/Rhilip/RidPT.svg"></a>
<img src="https://img.shields.io/badge/used-Swoole-blue.svg">
<a href="https://github.com/Rhilip/RidPT/LICENSE" title="GitHub license"><img src="https://img.shields.io/github/license/Rhilip/RidPT.svg"></a>
</p>

--------------------------------------

## Demo Site

Demo Url: http://ridpt.top/

Test Account Information:
 - Username : `Admin`
 - Password : `123456`

## Installation Guide

1. We test our RidPT project on this environment :

    - Nginx 1.14.2
    - MySQL 8.0.14
    - PHP 7.3.1 (With other extension which your can see in `composer.json`)
    - Swoole 4.2.12
    - Redis 5.0.3 Stable
    - Bower 1.8.4 (A package manager for the web)
 
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

5. Run Test by `php bin/rid-httpd service start -u` , And Congratulation If you see those output **without error throwout**.

    ```bash
    root@ridpt:/data/wwwroot/ridpt.rhilip.info# php bin/rid-httpd service start
     ____            __  ____    ______   
    /\  _`\   __    /\ \/\  _`\ /\__  _\  
    \ \ \L\ \/\_\   \_\ \ \ \L\ \/_/\ \/  
     \ \ ,  /\/\ \  /'_` \ \ ,__/  \ \ \  
      \ \ \ \ \ \/\ \L\ \ \ \/    \ \ \ 
       \ \_\ \_\ \_\ \___,_\ \_\     \ \_\
        \/_/\/ /\/_/\/__,_ /\/_/      \/_/
    ───────────────────────────────────────
    Server      Name:      rid-httpd
    System      Name:      linux
    Framework   Version:   v0.1.2-alpha
    PHP         Version:   7.3.1
    Swoole      Version:   4.2.12
    Listen      Addr:      127.0.0.1
    Listen      Port:      9501
    Reactor     Num:       1
    Worker      Num:       20
    Hot         Update:    enabled
    Coroutine   Mode:      disabled
    Config      File:      /data/wwwroot/ridpt.rhilip.info/apps/config/http_permanent.php
    ───────────────────────────────────────
    ```

6. Then you can safely add Nginx reserve proxy config like `migration/nginx.conf`.And Notice : 
If your service is behind the CDN like Cloudflare, You must follow [How do I restore original visitor IP with Nginx?](https://support.cloudflare.com/hc/en-us/articles/200170706-How-do-I-restore-original-visitor-IP-with-Nginx)
So that tracker can record the peer's ip address.

7. Use the default `php bin/rid-httpd service start -d` to let *RidPT* RUN in the background. Or you can use other daemon work like:
    - Systemctl: [ridpt.service](migration/ridpt.service)

## Basie Environment in `.env`

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

## Dynamic Config

> Notice: Most Dynamic config note you can found in our config setting Page

// TODO

## Development Help

Some rule or Docs May help you when you rebuild this project,
Or you can join our chat group on Telegram -- [@ridpt](https://t.me/ridpt)

### FrontEnd

| Library | Docs |
|:--|:--|
| [Zui](https://github.com/easysoft/zui): an HTML5 front UI framework | http://zui.sexy/  ( Chinese Version ) |
| [FortAwesome](https://github.com/FortAwesome/Font-Awesome): The iconic SVG, font, and CSS toolkit | https://fontawesome.com/icons?d=gallery |
| [flag-css](https://github.com/7kfpun/flag-css): CSS for SVG country flags respecting the original ratio. | https://kfpun.com/flag-css/ |
| [zxcvbn](https://github.com/dropbox/zxcvbn): Low-Budget Password Strength Estimation | https://lowe.github.io/tryzxcvbn/ |

### Backend Library

| Library | Used As | Docs | 
|:--|:--:|:--|
| [MixPHP](https://github.com/mix-php/mix-framework/tree/v1) | Framework | https://www.kancloud.cn/onanying/mixphp1/379324 ( Chinese Version ) |
| [siriusphp/validation](https://github.com/siriusphp/validation) | Validator | http://www.sirius.ro/php/sirius/validation/ |
| [league/plates](https://github.com/thephpleague/plates) | Template system | http://platesphp.com/ |

