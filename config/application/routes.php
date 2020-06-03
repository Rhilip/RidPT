<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

use App\Controllers;
use App\Middleware;

use Rid\Http\Route\RouteCollector;

return function (RouteCollector $r) {
    // 增加全局Middleware
    $r->addMiddleware([
        Middleware\IpBanMiddleware::class
    ], function (RouteCollector $r) {
        // 一些不需要中间件保护的路由
        $r->get('/captcha', [Controllers\CaptchaController::class, 'index']);
        $r->get('/maintenance', [Controllers\MaintenanceController::class, 'index']);

        // Tracker部分
        $r->addGroup('/tracker', function (RouteCollector $r) {
            $r->get('/scrape', [Controllers\Tracker\ScrapeController::class, 'index']);
            $r->get('/announce', [Controllers\Tracker\AnnounceController::class, 'index']);
        });

        // Web访问部分
        $r->addMiddleware(Middleware\AuthMiddleware::class, function (RouteCollector $r) {
            // 测试路由
            $r->get('/test', [Controllers\TestController::class, 'index']);

            // 主页服务
            $r->get('/[index]', [Controllers\IndexController::class, 'index']);

            // 友情链接部分
            $r->addGroup('/links', function (RouteCollector $r) {
                $r->get('/apply', [Controllers\Links\ApplyController::class, 'index']);
                $r->get('/manage', [Controllers\Links\ManagerController::class, 'index']);

                $r->post('/apply', [Controllers\Links\ApplyController::class, 'takeApply']);
                $r->post('/edit', [Controllers\Links\ManagerController::class, 'takeEdit']);
                $r->get('/delete', [Controllers\Links\ManagerController::class, 'takeDelete']); // FIXME it should be post method
            });

            // 用户认证部分
            $r->addGroup('/auth', function (RouteCollector $r) {
                $r->get('/login', [Controllers\Auth\LoginController::class, 'index']);
                $r->get('/register', [Controllers\Auth\RegisterController::class, 'index']);
                $r->get('/recover', [Controllers\Auth\RecoverController::class, 'index']);

                $r->post('/login', [Controllers\Auth\LoginController::class, 'takeLogin']);
                $r->post('/register', [Controllers\Auth\RegisterController::class, 'takeRegister']);
                $r->post('/recover', [Controllers\Auth\RecoverController::class, 'takeRecover']);

                $r->addGroup('/confirm', function (RouteCollector $r) {
                    $r->get('/register', [Controllers\Auth\ConfirmController::class, 'register']);
                    $r->get('/recover', [Controllers\Auth\ConfirmController::class, 'recover']);
                });

                // auth路由下 唯一可以让已登录用户访问的，其他的应该在 AuthMiddleware 中拒绝
                $r->get('/logout', [Controllers\Auth\LogoutController::class, 'index']);
            });

            // 用户管理部分
            $r->addGroup('/user', function (RouteCollector $r) {
                $r->get('[/details]', [Controllers\UserController::class, 'details']);
                $r->get('/setting', [Controllers\UserController::class, 'setting']);
                $r->get('/invite', [Controllers\UserController::class, 'invite']);
                $r->get('/sessions', [Controllers\UserController::class, 'sessions']);
            });

            // FIXME 种子部分
            $r->addGroup('/torrent', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/upload', [Controllers\TorrentController::class, 'upload']);
                $r->get('/details', [Controllers\TorrentController::class, 'details']);
                $r->addRoute(['GET', 'POST'], '/edit', [Controllers\TorrentController::class, 'edit']);
                $r->get('/snatch', [Controllers\TorrentController::class, 'snatch']);
                $r->get('/download', [Controllers\TorrentController::class, 'download']);
                $r->get('/comments', [Controllers\TorrentController::class, 'comments']);
                $r->get('/structure', [Controllers\TorrentController::class, 'structure']);
            });
            $r->addGroup('/torrents', function (RouteCollector $r) {
                $r->get('[/search]', [Controllers\TorrentsController::class, 'search']);
                $r->get('/tags', [Controllers\TorrentsController::class, 'tags']);
            });

            // RSS部分
            $r->addGroup('/rss', function (RouteCollector $r) {
                $r->get('', [Controllers\RssController::class, 'index']);
            });

            // 字幕部分
            $r->addGroup('/subtitles', function (RouteCollector $r) {
                $r->get('[/search]', [Controllers\SubtitlesController::class, 'search']);
                $r->get('/upload', [Controllers\SubtitlesController::class, 'upload']);
                $r->get('/download', [Controllers\SubtitlesController::class, 'download']);
                $r->get('/delete', [Controllers\SubtitlesController::class, 'delete']);
            });

            // 站点新闻部分
            $r->addGroup('/blogs', function (RouteCollector $r) {
                $r->get('[/search]', [Controllers\Blogs\SearchController::class, 'index']);
                $r->get('/create', [Controllers\Blogs\CreateController::class, 'index']);
                $r->get('/edit', [Controllers\Blogs\EditController::class, 'index']);

                $r->post('/create', [Controllers\Blogs\CreateController::class, 'takeCreate']);
                $r->post('/edit', [Controllers\Blogs\EditController::class, 'takeEdit']);
                $r->post('/delete', [Controllers\Blogs\DeleteController::class, 'takeDelete']);
            });

            // 站点规则部分
            $r->addGroup('/site', function (RouteCollector $r) {
                $r->get('/rules', [Controllers\SiteController::class, 'rules']);
                $r->get('/logs', [Controllers\SiteController::class, 'logs']);
            });

            // 站点管理部分
            $r->addGroup('/manager', function (RouteCollector $r) {
                $r->addRoute(['GET', 'POST'], '/categories', [Controllers\ManageController::class, 'categories']);
            });

            // 管理员部分
            $r->addGroup('/admin', function (RouteCollector $r) {
                $r->get('', [Controllers\Admin\IndexController::class, 'index']);
                $r->addGroup('/service', function (RouteCollector $r) {
                    $r->get('/redis', [Controllers\Admin\Service\RedisController::class, 'index']);
                    $r->get('/mysql', [Controllers\Admin\Service\MysqlController::class, 'index']);
                });
            });
        });

        // API部分
        $r->addGroup('/api', function (RouteCollector $r) {
            // v1 部分路由不遵守Restful、Graphql等设计规范，给默认后端渲染页面提供ajax支持
            $r->addGroup('/v1', function (RouteCollector $r) {
                $r->addMiddleware([
                    Middleware\AuthMiddleware::class,
                    Middleware\ApiMiddleware::class
                ], function (RouteCollector $r) {
                    $r->addGroup('/torrent', function (RouteCollector $r) {
                        $r->post('/bookmark', [Controllers\Api\v1\TorrentController::class, 'bookmark']);
                        $r->get('/filelist', [Controllers\Api\v1\TorrentController::class, 'fileList']);
                        $r->get('/nfofilecontent', [Controllers\Api\v1\TorrentController::class, 'nfoFileContent']);
                    });
                });
            });
        });
    });
};
