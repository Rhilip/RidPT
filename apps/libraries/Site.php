<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/29
 * Time: 13:07
 */

namespace apps\libraries;


use Rid\Http\View;
use Rid\Utils\ClassValueCacheUtils;

class Site
{
    use ClassValueCacheUtils;

    const LOG_LEVEL_NORMAL = 'normal';
    const LOG_LEVEL_MOD = 'mod';
    const LOG_LEVEL_SYSOP = 'sysop';
    const LOG_LEVEL_LEADER = 'leader';

    protected static function getStaticCacheNameSpace(): string
    {
        return 'Cache:site';
    }

    public static function writeLog($msg, $level = self::LOG_LEVEL_NORMAL)
    {
        app()->pdo->createCommand("INSERT INTO `site_log`(`create_at`,`msg`, `level`) VALUES (CURRENT_TIMESTAMP,:msg,:level)")->bindParams([
            "msg" => $msg, "level" => $level
        ])->execute();
    }

    public static function sendPM($sender, $receiver, $subject, $msg, $save = "no", $location = 1)
    {
        app()->pdo->createCommand("INSERT `messages` (`sender`,`receiver`,`add_at`, subject, msg, saved, location) VALUES (:sender,:receiver,CURRENT_TIMESTAMP,:subject,:msg,:save,:location)")->bindParams([
            "sender" => $sender, "receiver" => $receiver,
            "subject" => $subject, "msg" => $msg,
            "save" => $save, "location" => $location
        ])->execute();

        // FIXME redis key
        app()->redis->del('user_' . $receiver . '_unread_message_count');
        app()->redis->del('user_' . $receiver . '_inbox_count');
        if ($sender != 0) app()->redis->del('user_' . $sender . '_outbox_count');
    }

    public static function sendEmail($receivers, $subject, $template, $data = [])
    {
        $mail_body = (new View(false))->render($template, $data);
        $mail_sender = Mailer::newInstanceByConfig('libraries.[mailer]');
        $mail_sender->send($receivers, $subject, $mail_body);
    }

    public static function getQualityTableList()
    {
        return [
            'audio' => 'Audio Codec',  // TODO i18n title
            'codec' => 'Codec',
            'medium' => 'Medium',
            'resolution' => 'Resolution'
        ];
    }

    public static function ruleCategory(): array
    {
        return static::getStaticCacheValue('enabled_torrent_category', function () {
            return app()->pdo->createCommand('SELECT * FROM `categories` WHERE `id` > 0 ORDER BY `full_path`')->queryAll();
        }, 86400);
    }

    public static function ruleCanUsedCategory(): array
    {
        return array_filter(static::ruleCategory(), function ($cat) {
            return $cat['enabled'] = 1;
        });
    }

    public static function ruleQuality($quality): array
    {
        if (!in_array($quality, array_keys(self::getQualityTableList()))) throw new \RuntimeException('Unregister quality : ' . $quality);
        return static::getStaticCacheValue('enabled_quality_' . $quality, function () use ($quality) {
            return app()->pdo->createCommand("SELECT * FROM `quality_$quality` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`")->queryAll();
        }, 86400);
    }

    public static function ruleTeam(): array
    {
        return static::getStaticCacheValue('enabled_teams', function () {
            return app()->pdo->createCommand('SELECT * FROM `teams` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`')->queryAll();
        }, 86400);
    }

    public static function ruleCanUsedTeam(): array
    {
        return array_filter(static::ruleTeam(), function ($team) {
            return app()->user->getClass(true) >= $team['class_require'];
        });
    }

    public static function rulePinnedTags(): array
    {
        return static::getStaticCacheValue('pinned_tags', function () {
            return app()->pdo->createCommand('SELECT * FROM `tags` WHERE `pinned` = 1 LIMIT 10;')->queryAll();
        }, 86400);
    }

    public static function fetchUserCount(): int
    {
        return app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users`")->queryScalar();
    }
}
