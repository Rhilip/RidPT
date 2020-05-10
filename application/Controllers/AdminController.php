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
    public function index()
    {
        return $this->render('admin/index');
    }

    public function redis()
    {
        $info = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->info();
        $dbsize = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->dbSize();

        /** @var array $cmdstat_raw */
        $cmdstat_raw = \Rid\Helpers\ContainerHelper::getContainer()->get('redis')->info('commandstats');

        $cmdstat = array_map(function ($v) {
            preg_match('/calls=(?P<calls>\d+),usec=(?P<usec>\d+),usec_per_call=(?P<usec_per_call>[\d\.]+)/', $v, $m);
            return $m;
        }, $cmdstat_raw);

        return $this->render('admin/redis_status', ['info' => $info, 'dbsize' => $dbsize, 'cmdstat' => $cmdstat]);
    }

    public function mysql()
    {
        $res = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SHOW GLOBAL STATUS')->queryAll();
        $serverStatus = array_column($res, 'Value', 'Variable_name');
        $startAt = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT UNIX_TIMESTAMP() - :uptime')->bindParams([
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
