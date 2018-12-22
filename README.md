## RidPT

A Private Torrent Project based on `MixPHP` framework.

## Installation Guide

1. We test our RidPT project on this environment :

 - Nginx
 - MySQL 5.7.22 (With InnoDB Storage Engine)
 - PHP 7.2.6 (With other extension which your can see in `composer.json`)
 - Swoole 4.2.9
 - Redis 4.0.9 Stable
 
2. After you prepare those base environment well, 
 - run below command to clone this repo or you can download from our release.
 ```bash
git clone https://github.com/Rhilip/RidPT.git /home/wwwroot/your.domain.com
cd /home/wwwroot/your.domain.com/
composer install
```
 - And use `composer install` Install dependency.
 - If you don't run RidPT apps in `root` user, you should give enough permission to `apps/httpd/{runtime,private}`.

3. Import Database from our `migration/ridpt.sql`, and **disable Mysql strict mode `NO_ZERO_IN_DATE` and `NO_ZERO_DATE`**.

```bash
mysql -u root -p < migration/ridpt.sql
```
 
4. Then set your RidPT Project config (about APP, DATABASE, Redis, Mailer) in `.env`. The other config about site your can 
edit in Admin Panel.

```bash
cp .env.example .env
vi .env
```

5. Run Test by `php mix-httpd service start -u` , And Congratulation If you see those output **without error throwout**.

```bash
root@ubuntu-s-4vcpu-8gb-nyc1-01:/home/wwwroot/ridpt.rhilip.info# php bin/mix-httpd service start -u
                           _____
_______ ___ _____ ___ _____  / /_  ____
__/ __ `__ \/ /\ \/ / / __ \/ __ \/ __ \
_/ / / / / / / /\ \/ / /_/ / / / / /_/ /
/_/ /_/ /_/_/ /_/\_\/ .___/_/ /_/ .___/
                   /_/         /_/

Server      Name:      mix-httpd
Framework   Version:   1.1.1
PHP         Version:   7.2.6
Swoole      Version:   4.2.10
Listen      Addr:      ::
Listen      Port:      9501
Hot         Update:    enabled
Coroutine   Mode:      disabled
Config      File:      /home/wwwroot/ridpt.rhilip.info/apps/httpd/config/http_permanent.php
```

6. Then you can safely add Nginx reserve proxy config like `migration/nginx.conf`.And Notice : 
If your service is behind the CDN like Cloudflare, You must follow [How do I restore original visitor IP with Nginx?](https://support.cloudflare.com/hc/en-us/articles/200170706-How-do-I-restore-original-visitor-IP-with-Nginx)
So that tracker can record the peer's ip address.

## Basie Environment in `.env`

TODO
