<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/8
 * Time: 10:49
 */

namespace apps\common\libraries;


use mix\facades\PDO;

class SiteLog
{
    const LEVEL_NORMAL = 'normal';
    const LEVEL_MOD = 'mod';
    const LEVEL_SYSOP = 'sysop';
    const LEVEL_LEADER = 'leader';

    public static function write($msg, $level = self::LEVEL_NORMAL)
    {
        PDO::createCommand("INSERT INTO `site_log`(`msg`, `level`) VALUES (:msg,:level)")->bindParams([
            "msg" => $msg, "level" => $level
        ]);
    }
}