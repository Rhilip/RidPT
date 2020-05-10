<?php
/**
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

use Rid\Http\Route\RouteCollector;

return function (RouteCollector $r) {
    // 增加全局Middleware
    $r->addMiddleware([
        \App\Middleware\IpBanMiddleware::class
    ], function (RouteCollector $r) {
        // 一些不需要中间件保护的路由
        $r->get('/captcha', [\App\Controllers\CaptchaController::class, 'index']);
        $r->get('/maintenance', [\App\Controllers\MaintenanceController::class, 'index']);

        // FIXME Tracker部分
        $r->get('/tracker/{action:(?:scrape|announce)}', [\App\Controllers\TrackerController::class, 'index']);

        // Web访问部分
        $r->addMiddleware(\App\Middleware\AuthMiddleware::class, function (RouteCollector $r) {
            // 测试路由
            $r->get('/test', [\App\Controllers\TestController::class, 'index']);

            // 主页服务
            $r->get('/[index]', [\App\Controllers\IndexController::class, 'index']);

            // 友情链接部分
            $r->addGroup('/links', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/apply', [\App\Controllers\LinksController::class, 'apply']);
                $r->addRoute(['GET', 'POST'], '/manager', [\App\Controllers\LinksController::class, 'manager']);
            });

            // 用户认证部分
            $r->addGroup('/auth', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/login', [\App\Controllers\AuthController::class, 'login']);
                $r->addRoute(['GET', 'POST'], '/logout', [\App\Controllers\AuthController::class, 'logout']);
                $r->addRoute(['GET', 'POST'], '/recover', [\App\Controllers\AuthController::class, 'recover']);
                $r->addRoute(['GET', 'POST'], '/confirm', [\App\Controllers\AuthController::class, 'confirm']);
                $r->addRoute(['GET', 'POST'], '/register', [\App\Controllers\AuthController::class, 'register']);
            });

            // 用户管理部分
            $r->addGroup('/user', function (RouteCollector $r) {
                $r->get('[/details]', [\App\Controllers\UserController::class, 'details']);
                $r->get('/setting', [\App\Controllers\UserController::class, 'setting']);
                $r->get('/invite', [\App\Controllers\UserController::class, 'invite']);
                $r->get('/sessions', [\App\Controllers\UserController::class, 'sessions']);
            });

            // FIXME 种子部分
            $r->addGroup('/torrent', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/upload', [\App\Controllers\TorrentController::class, 'upload']);
                $r->get('/details', [\App\Controllers\TorrentController::class, 'details']);
                $r->addRoute(['GET', 'POST'], '/edit', [\App\Controllers\TorrentController::class, 'edit']);
                $r->get('/snatch', [\App\Controllers\TorrentController::class, 'snatch']);
                $r->get('/download', [\App\Controllers\TorrentController::class, 'download']);
                $r->get('/comments', [\App\Controllers\TorrentController::class, 'comments']);
                $r->get('/structure', [\App\Controllers\TorrentController::class, 'structure']);
            });
            $r->addGroup('/torrents', function (RouteCollector $r) {
                $r->get('[/search]', [\App\Controllers\TorrentsController::class, 'search']);
                $r->get('/tags', [\App\Controllers\TorrentsController::class, 'tags']);
            });

            // RSS部分
            $r->addGroup('/rss', function (RouteCollector $r) {
                $r->get('/', [\App\Controllers\RssController::class, 'index']);
            });

            // 字幕部分
            $r->addGroup('/subtitles', function (RouteCollector $r) {
                $r->get('[/search]', [\App\Controllers\SubtitlesController::class, 'search']);
                $r->get('/upload', [\App\Controllers\SubtitlesController::class, 'upload']);
                $r->get('/download', [\App\Controllers\SubtitlesController::class, 'download']);
                $r->get('/delete', [\App\Controllers\SubtitlesController::class, 'delete']);
            });

            // 站点新闻部分
            $r->addGroup('/news', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/', [\App\Controllers\NewsController::class, 'index']);
                $r->addRoute(['GET', 'POST'], '/new', [\App\Controllers\NewsController::class, 'new']);
                $r->addRoute(['GET', 'POST'], '/edit', [\App\Controllers\NewsController::class, 'edit']);
                $r->addRoute(['GET', 'POST'], '/delete', [\App\Controllers\NewsController::class, 'delete']);
            });

            // 站点规则部分
            $r->addGroup('/site', function (RouteCollector $r) {
                $r->get('/rules', [\App\Controllers\SiteController::class, 'rules']);
                $r->get('/logs', [\App\Controllers\SiteController::class, 'logs']);
            });

            // 站点管理部分
            $r->addGroup('/manager', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/categories', [\App\Controllers\ManageController::class, 'categories']);
            });


            // 管理员部分
            $r->addGroup('/admin', function (RouteCollector $r) {
                $r->get('/', [\App\Controllers\AdminController::class, 'index']);
                $r->addGroup('/service', function (RouteCollector $r) {
                    $r->get('/redis', [\App\Controllers\AdminController::class, 'redis']);
                    $r->get('/mysql', [\App\Controllers\AdminController::class, 'mysql']);
                });
            });
        });

        // API部分
        $r->addGroup('/api', function (RouteCollector $r) {
            // v1 部分
            $r->addGroup('/v1', function (RouteCollector $r) {
                $r->addMiddleware([
                    \App\Middleware\AuthMiddleware::class,
                    \App\Middleware\ApiMiddleware::class
                ], function (RouteCollector $r) {
                    $r->addGroup('/torrent', function (RouteCollector $r) {
                        $r->post('/bookmark', [\App\Controllers\Api\v1\TorrentController::class, 'bookmark']);
                        $r->get('/filelist', [\App\Controllers\Api\v1\TorrentController::class, 'fileList']);
                        $r->get('/nfofilecontent', [\App\Controllers\Api\v1\TorrentController::class, 'nfoFileContent']);
                    });
                });
            });
        });
    });
};
