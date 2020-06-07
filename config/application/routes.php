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

        // Web访问部分，只使用 GET或POST 方法，不使用其他请求方法
        $r->addMiddleware(Middleware\AuthMiddleware::class, function (RouteCollector $r) {
            // 测试路由
            $r->get('/test', [Controllers\TestController::class, 'index']);

            // 主页服务
            $r->get('/[index]', [Controllers\IndexController::class, 'index']);

            // 管理员部分
            $r->addGroup('/admin', function (RouteCollector $r) {
                $r->get('', [Controllers\Admin\IndexController::class, 'index']);
                $r->addGroup('/service', function (RouteCollector $r) {
                    $r->get('/redis', [Controllers\Admin\Service\RedisController::class, 'index']);
                    $r->get('/mysql', [Controllers\Admin\Service\MysqlController::class, 'index']);
                });
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

            // 站点新闻部分
            $r->addGroup('/blogs', function (RouteCollector $r) {
                $r->get('[/search]', [Controllers\Blogs\SearchController::class, 'index']);
                $r->get('/create', [Controllers\Blogs\CreateController::class, 'index']);
                $r->get('/edit', [Controllers\Blogs\EditController::class, 'index']);

                $r->post('/create', [Controllers\Blogs\CreateController::class, 'takeCreate']);
                $r->post('/edit', [Controllers\Blogs\EditController::class, 'takeEdit']);
                $r->post('/delete', [Controllers\Blogs\DeleteController::class, 'takeDelete']);
            });

            // 邀请部分
            $r->addGroup('/invite', function (RouteCollector $r) {
                $r->get('', [Controllers\Invite\IndexController::class, 'index']);
                $r->post('', [Controllers\Invite\IndexController::class, 'takeInvite']);

                $r->post('/confirm', [Controllers\Invite\ConfirmController::class, 'takeConfirm']);
                $r->post('/recycle', [Controllers\Invite\RecycleController::class, 'takeRecycle']);
            });

            // 友情链接部分
            $r->addGroup('/links', function (RouteCollector $r) {
                $r->get('/apply', [Controllers\Links\ApplyController::class, 'index']);
                $r->get('/manage', [Controllers\Links\ManagerController::class, 'index']);

                $r->post('/apply', [Controllers\Links\ApplyController::class, 'takeApply']);
                $r->post('/edit', [Controllers\Links\ManagerController::class, 'takeEdit']);
                $r->get('/delete', [Controllers\Links\ManagerController::class, 'takeDelete']); // FIXME it should be post method
            });

            // 站点管理部分
            $r->addGroup('/manage', function (RouteCollector $r) {
                // 分类板块部分
                $r->addGroup('/categories', function (RouteCollector $r) {
                    $r->get('', [Controllers\Manage\Categories\IndexController::class, 'index']);

                    $r->post('/edit', [Controllers\Manage\Categories\EditController::class, 'takeEdit']);
                    $r->post('/delete', [Controllers\Manage\Categories\DeleteController::class, 'takeDelete']);
                });
            });

            // 站点规则部分
            $r->addGroup('/site', function (RouteCollector $r) {
                $r->get('/rules', [Controllers\Site\RulesController::class, 'index']);
                $r->get('/logs', [Controllers\Site\LogsController::class, 'index']);
            });

            // 字幕部分
            $r->addGroup('/subtitles', function (RouteCollector $r) {
                $r->get('[/search]', [Controllers\Subtitles\SearchController::class, 'index']);
                $r->get('/upload', [Controllers\Subtitles\UploadController::class, 'index']);
                $r->get('/download', [Controllers\Subtitles\DownloadController::class, 'index']);

                $r->post('/upload', [Controllers\Subtitles\UploadController::class, 'takeUpload']);
                $r->post('/delete', [Controllers\Subtitles\DeleteController::class, 'takeDelete']);
            });

            // RSS部分
            $r->addGroup('/rss', function (RouteCollector $r) {
                $r->get('[/feed]', [Controllers\Rss\FeedController::class, 'index']);
            });

            // 种子部分
            $r->addGroup('/torrents', function (RouteCollector $r) {
                // 对单个种子
                $r->get('/upload', [Controllers\Torrents\UploadController::class, 'index']);
                $r->get('/detail', [Controllers\Torrents\DetailController::class, 'index']);
                $r->get('/edit', [Controllers\Torrents\EditController::class, 'index']);
                $r->get('/structure', [Controllers\Torrents\StructureController::class, 'index']);
                $r->get('/download', [Controllers\Torrents\DownloadController::class, 'index']);
                $r->get('/snatch', [Controllers\Torrents\SnatchController::class, 'index']);
                $r->get('/nfo', [Controllers\Torrents\NfoController::class, 'index']);

                $r->post('/upload', [Controllers\Torrents\UploadController::class, 'takeUpload']);
                $r->post('/edit', [Controllers\Torrents\EditController::class, 'takeEdit']);

                // FIXME $r->get('/comments', [Controllers\TorrentController::class, 'comments']);

                // 种子列表
                $r->get('[/search]', [Controllers\Torrents\SearchController::class, 'index']);
                $r->get('/tags', [Controllers\Torrents\TagsController::class, 'index']);
            });


            // FIXME 待修改部分

            // 用户管理部分
            $r->addGroup('/user', function (RouteCollector $r) {
                $r->get('[/details]', [Controllers\UserController::class, 'details']);
                $r->get('/setting', [Controllers\UserController::class, 'setting']);
                $r->get('/sessions', [Controllers\UserController::class, 'sessions']);
            });
        });

        // API部分
        $r->addGroup('/api', function (RouteCollector $r) {
            // v1 部分路由不遵守Restful、Graphql等设计规范，同样仅使用 GET和POST 方法，给默认后端渲染页面提供ajax支持
            $r->addGroup('/v1', function (RouteCollector $r) {
                $r->addMiddleware([
                    Middleware\AuthMiddleware::class,
                    Middleware\ApiMiddleware::class
                ], function (RouteCollector $r) {
                    $r->addGroup('/torrent', function (RouteCollector $r) {
                        $r->get('/filelist', [Controllers\Api\v1\Torrent\FileListController::class, 'index']);
                        $r->post('/bookmark', [Controllers\Api\v1\Torrent\BookmarkController::class, 'takeBookmark']);
                    });
                });
            });

            // TODO v2 部分路由遵守Restful设计规范
        });
    });
};
