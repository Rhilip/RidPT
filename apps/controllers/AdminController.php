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
                return $this->render('errors/action_fail.html.twig',['title'=>'Not Support Action','msg'=>'not support']);
        }

    }

    private function infoRedis() {
        // TODO
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
