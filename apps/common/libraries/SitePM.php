<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/8
 * Time: 10:57
 */

namespace apps\common\libraries;


use mix\Facades\PDO;
use mix\Facades\Redis;

class SitePM
{
    public static function send($sender, $receiver, $subject, $msg, $save = "no", $location = 1) {
        PDO::createCommand("INSERT messages (sender, receiver, subject, msg, saved, location) VALUES(:sender,:receiver,:subject,:msg,:save,:location)")->bindParams([
            "sender" => $sender, "receiver" => $receiver,
            "subject" => $subject , "msg" => $msg,
            "save" => $save , "location" => $location
        ])->execute();

        Redis::del('user_' . $receiver . '_unread_message_count');
        Redis::del('user_' . $receiver . '_inbox_count');
        if ($sender != 0) Redis::del('user_' . $sender . '_outbox_count');
    }
}