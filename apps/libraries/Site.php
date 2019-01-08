<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 13:07
 */

namespace apps\libraries;


class Site
{
    const LOG_LEVEL_NORMAL = 'normal';
    const LOG_LEVEL_MOD = 'mod';
    const LOG_LEVEL_SYSOP = 'sysop';
    const LOG_LEVEL_LEADER = 'leader';

    public static function writeLog($msg, $level = self::LOG_LEVEL_NORMAL)
    {
        app()->pdo->createCommand("INSERT INTO `site_log`(`msg`, `level`) VALUES (:msg,:level)")->bindParams([
            "msg" => $msg, "level" => $level
        ])->execute();
    }

    public static function sendPM($sender, $receiver, $subject, $msg, $save = "no", $location = 1) {
        app()->pdo->createCommand("INSERT messages (sender, receiver, subject, msg, saved, location) VALUES(:sender,:receiver,:subject,:msg,:save,:location)")->bindParams([
            "sender" => $sender, "receiver" => $receiver,
            "subject" => $subject , "msg" => $msg,
            "save" => $save , "location" => $location
        ])->execute();
        
        // FIXME redis key
        app()->redis->del('user_' . $receiver . '_unread_message_count');
        app()->redis->del('user_' . $receiver . '_inbox_count');
        if ($sender != 0) app()->redis->del('user_' . $sender . '_outbox_count');
    }

    public static function fetchUserCount() {
        return app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users`")->queryScalar();
    }
}
