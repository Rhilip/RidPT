<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/1
 * Time: 16:24
 */

namespace apps\controllers;


use Mix\Http\Controller;

class AdminController extends Controller
{
    public function actionIndex()
    {
        return $this->render('admin/index.html.twig');
    }

    public function actionService()
    {
        $provider = app()->request->get('provider');
        switch (strtolower($provider)) {
            case 'mysql':
                return $this->infoMysql();
            case 'redis':
                return $this->infoRedis();
            default:
                return $this->render('errors/action_fail.html.twig', ['title' => 'Not Support Action', 'msg' => 'not support']);
        }

    }

    private function infoRedis()
    {
        $panel = app()->request->get('panel') ?? 'status';

        if ($panel === 'keys') {
            if (app()->request->isPost()) {
                $action = app()->request->post('action');
                if ($action == 'delkey') {
                    $delkey = app()->request->post('key');
                    app()->redis->del($delkey);
                } elseif ($action == 'delkeys') {
                    $pattern = app()->request->post('keypattern');
                    app()->redis->del(app()->redis->keys($pattern));
                }
            }
            $offset = app()->request->get('offset') ?? null;
            $perpage = app()->request->get('perpage') ?? 50;
            $pattern = app()->request->get('pattern') ?? '*';

            $keys = app()->redis->keys($pattern);
            ksort($keys);
            $limited_keys = array_slice($keys, $offset * $perpage, $perpage);

            $types = [];
            foreach ($limited_keys as $key) {
                $types[$key] = app()->redis->typeof($key);
            }

            return $this->render('admin/redis_keys.html.twig', [
                'offset' => $offset,
                'perpage' => $perpage,
                'pattern' => $pattern,
                'keys' => $limited_keys,
                'types' => $types,
                'num_keys' => count($keys)
            ]);
        } elseif ($panel === 'key') {
            $key = app()->request->get('key');
            $dump = app()->redis->dump($key);
            if ($dump === false) {
                return app()->response->setStatusCode(404);
            }
            $size = strlen($dump);
            $t = app()->redis->typeof($key);
            $ttl = app()->redis->ttl($key);
            if ($t == 'String') {
                $val = app()->redis->get($key);
            } elseif ($t == 'List') {
                $val = app()->redis->lRange($key, 0, -1);
            } elseif ($t == 'Hash') {
                $val = app()->redis->hGetAll($key);
            } elseif ($t == 'Set') {
                $val = app()->redis->sMembers($key);
            } elseif ($t == 'Sorted Set') {
                $val = app()->redis->zRange($key, 0, -1, true);
            } else {
                $val = '';
            }
            return $this->render('admin/redis_key.html.twig', [
                'key' => $key,
                'value' => $val,
                'type' => $t,
                'size' => $size,
                'ttl' => $ttl,
                'expiration' => time() + $ttl,
            ]);
        } else {  // &panel=status
            $info = app()->redis->info();
            $dbsize = app()->redis->dbSize();
            $cmdstat_raw = app()->redis->info('commandstats');

            $cmdstat = array_map(function ($v) {
                preg_match('/calls=(?P<calls>\d+),usec=(?P<usec>\d+),usec_per_call=(?P<usec_per_call>[\d\.]+)/', $v, $m);
                return $m;
            }, $cmdstat_raw);

            return $this->render('admin/redis_status.html.twig', ['info' => $info, 'dbsize' => $dbsize, 'cmdstat' => $cmdstat]);
        }
    }

    private function infoMysql()
    {
        $res = app()->pdo->createCommand('SHOW GLOBAL STATUS')->queryAll();
        $res = array_map(function ($s) {
            $s['Variable_name'] = str_replace('_', ' ', $s['Variable_name']);
            return $s;
        }, $res);
        $serverStatus = array_column($res, 'Value', 'Variable_name');
        $startAt = app()->pdo->createCommand('SELECT UNIX_TIMESTAMP() - :uptime')->bindParams([
            'uptime' => $serverStatus['Uptime']
        ])->queryScalar();
        $queryStats = [];
        $tmp_array = $serverStatus;
        foreach ($tmp_array AS $name => $value) {
            if (substr($name, 0, 4) == 'Com ') {
                $queryStats[substr($name, 4)] = $value;
                unset($serverStatus[$name]);
            }
        }

        return $this->render('admin/mysql_status.html.twig', [
            'serverStatus' => $serverStatus,
            'startAt' => $startAt,
            'queryStats' => $queryStats,
        ]);
    }
}
