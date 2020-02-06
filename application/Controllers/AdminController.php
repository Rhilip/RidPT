<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/1
 * Time: 16:24
 */

namespace App\Controllers;

use Rid\Http\Controller;

class AdminController extends Controller
{
    public function actionIndex()
    {
        return $this->render('admin/index');
    }

    public function actionService()
    {
        $provider = app()->request->query->get('provider');
        switch (strtolower($provider)) {
            case 'mysql':
                return $this->infoMysql();
            case 'redis':
                return $this->infoRedis();
            default:
                return $this->render('action/fail', ['title' => 'Not Support Action', 'msg' => 'not support']);
        }
    }

    private function infoRedis()
    {
        $info = app()->redis->info();
        $dbsize = app()->redis->dbSize();
        $cmdstat_raw = app()->redis->info('commandstats');

        $cmdstat = array_map(function ($v) {
            preg_match('/calls=(?P<calls>\d+),usec=(?P<usec>\d+),usec_per_call=(?P<usec_per_call>[\d\.]+)/', $v, $m);
            return $m;
        }, $cmdstat_raw);

        return $this->render('admin/redis_status', ['info' => $info, 'dbsize' => $dbsize, 'cmdstat' => $cmdstat]);
    }

    private function infoMysql()
    {
        $res = app()->pdo->prepare('SHOW GLOBAL STATUS')->queryAll();
        $serverStatus = array_column($res, 'Value', 'Variable_name');
        $startAt = app()->pdo->prepare('SELECT UNIX_TIMESTAMP() - :uptime')->bindParams([
            'uptime' => $serverStatus['Uptime']
        ])->queryScalar();
        $queryStats = [];
        $tmp_array = $serverStatus;
        foreach ($tmp_array as $name => $value) {
            if (substr($name, 0, 4) == 'Com_') {
                $queryStats[substr($name, 4)] = $value;
                unset($serverStatus[$name]);
            }
        }

        return $this->render('admin/mysql_status', [
            'serverStatus' => $serverStatus,
            'startAt' => $startAt,
            'queryStats' => $queryStats,
        ]);
    }
}
