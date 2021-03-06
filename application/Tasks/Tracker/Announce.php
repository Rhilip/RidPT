<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Tasks\Tracker;

use App\Libraries\Constant;

use Rid\Helpers\IoHelper;
use Rid\Swoole\Task\Interfaces\TaskHandlerInterface;

class Announce implements TaskHandlerInterface
{
    public function handle(array $param, \Swoole\Server $server, int $taskId, int $workerId)
    {
        container()->get('dbal')->beginTransaction();
        try {
            /** We got data from Http Server Like
             * [
             *    'timestamp' => timestamp when controller receive the announce,
             *    'queries' => $queries, 'role' => $role,
             *    'userInfo' => $userInfo, 'torrentInfo' => $torrentInfo
             * ]
             */
            $this->processAnnounceRequest($param['timestamp'], $param['queries'], $param['role'], $param['userInfo'], $param['torrentInfo']);

            container()->get('dbal')->commit();
        } catch (\Exception $e) {
            IoHelper::getIo()->note($e->getMessage());
            container()->get('dbal')->rollback();
        }
    }

    public function finish(\Swoole\Server $server, int $taskId, $data)
    {
        // TODO: Implement finish() method.
    }

    /**
     * @param $timenow
     * @param $queries
     * @param $seeder
     * @param $userInfo
     * @param $torrentInfo
     */
    private function processAnnounceRequest($timenow, $queries, $seeder, $userInfo, $torrentInfo)
    {
        $timeKey = ($seeder == 'yes') ? 'seed_time' : 'leech_time';
        $torrentUpdateKey = ($seeder == 'yes') ? 'complete' : 'incomplete';
        $trueUploaded = $trueDownloaded = 0;
        $thisUploaded = $thisDownloaded = 0;

        // Try to fetch session from Table `peers`
        $self = container()->get('dbal')->prepare('SELECT `uploaded`, `downloaded`, UNIX_TIMESTAMP(`last_action_at`) as `last_action_at`
        FROM `peers` WHERE `user_id`=:uid AND `torrent_id`=:tid AND `peer_id`=:pid LIMIT 1;')->bindParams([
            'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id']
        ])->fetchOne();

        if ($self === false) {
            // If session is not exist and &event!=stopped, a new session should start
            if ($queries['event'] != 'stopped') {
                // Then create new session in database
                // Update `torrents`, if peer's role is a seeder ,so complete +1 , elseif  he is a leecher or partial seeder , so incomplete +1
                container()->get('dbal')->prepare("UPDATE `torrents` SET `{$torrentUpdateKey}` = `{$torrentUpdateKey}` +1 WHERE id=:tid")->bindParams([
                    'tid' => $torrentInfo['id']
                ])->execute();

                $trueUploaded = max(0, $queries['uploaded']);
                $trueDownloaded = max(0, $queries['downloaded']);

                container()->get('dbal')->prepare("INSERT INTO `peers` SET `user_id` =:uid, `torrent_id`= :tid, `peer_id`= :pid,
                        `ip` = :ip, `port` = :port, `endpoints` = :endpoints, `connect_type` = :connect_type,
                        `started_at`= FROM_UNIXTIME(:started_at), `last_action_at` = FROM_UNIXTIME(:last_action_at),
                        `agent`= :agent, `seeder` = :seeder,
                        `uploaded` = :upload, `downloaded` = :download, `to_go` = :to_go,
                        `corrupt` = :corrupt, `key` = :key;
                        ")->bindParams([
                    'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id'],
                    'ip' => $queries['ip'], 'port' => $queries['port'],
                    'endpoints' => json_encode($queries['endpoints']), 'connect_type' => $queries['connect_type'],
                    'agent' => $queries['user-agent'],
                    'upload' => $trueUploaded, 'download' => $trueDownloaded, 'to_go' => $queries['left'],
                    'started_at' => $timenow, 'last_action_at' => $timenow,
                    'seeder' => $seeder, 'corrupt' => $queries['corrupt'], 'key' => $queries['key'],
                ])->execute();

                // Search history record, and create new record if not exist.
                $selfRecordCount = container()->get('dbal')->prepare('SELECT COUNT(`id`) FROM snatched WHERE user_id = :uid AND torrent_id = :tid')->bindParams([
                    'uid' => $userInfo['id'],
                    'tid' => $torrentInfo['id']
                ])->fetchScalar();

                if ($selfRecordCount == 0) {
                    container()->get('dbal')->prepare("INSERT INTO snatched (`user_id`, `torrent_id`, `agent`, `ip`, `port`, `true_downloaded`, `true_uploaded`, `this_download`, `this_uploaded`, `to_go`, `{$timeKey}`, `create_at`, `last_action_at`)
                        VALUES (:uid,:tid,:agent,INET6_ATON(:ip),:port,:true_dl,:true_up,:this_dl,:this_up,:to_go,:time,FROM_UNIXTIME(:create_at),FROM_UNIXTIME(:last_action_at))")->bindParams([
                        'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'],
                        'agent' => $queries['user-agent'], 'ip' => $queries['ip'], 'port' => $queries['port'],
                        'true_up' => 0, 'true_dl' => 0,
                        'this_up' => 0, 'this_dl' => 0,
                        'to_go' => $queries['left'], 'time' => 0,
                        'create_at' => $timenow, 'last_action_at' => $timenow
                    ])->execute();
                }
            }
        } else {
            // So that , We can calculate Announce data on a exist session
            $trueUploaded = max(0, $queries['uploaded'] - $self['uploaded']);
            $trueDownloaded = max(0, $queries['downloaded'] - $self['downloaded']);
            $duration = max(0, $timenow - $self['last_action_at']);
            $upSpeed = (($trueUploaded > 0 && $duration > 0) ? $trueUploaded / $duration : 0);

            if (config('tracker.enable_upspeed_check')) {
                if ($userInfo['class'] < config('authority.pass_tracker_upspeed_check') && $duration > 0) {
                    $this->checkUpspeed($userInfo, $torrentInfo, $trueUploaded, $trueDownloaded, $duration, $upSpeed);
                }
            }

            $this->getTorrentBuff($userInfo['id'], $torrentInfo['id'], $trueUploaded, $trueDownloaded, $upSpeed, $thisUploaded, $thisDownloaded);

            // Update Table `peers`, `snatched` by it's event tag
            // Notice : there MUST have history record in Table `snatched` if session is exist !!!!!!!!
            if ($queries['event'] === 'stopped') {
                // Update `torrents`, if peer's role is a seeder ,so complete -1 , elseif  he is a leecher , so incomplete -1
                container()->get('dbal')->prepare("UPDATE `torrents` SET `{$torrentUpdateKey}` = `{$torrentUpdateKey}` -1 WHERE id=:tid")->bindParams([
                    'tid' => $torrentInfo['id']
                ])->execute();

                // Peer stop seeding or leeching and should remove this peer from our peer list and update his data.
                container()->get('dbal')->prepare('DELETE FROM `peers` WHERE `user_id` = :uid AND `torrent_id` = :tid AND `peer_id` = :pid')->bindParams([
                    'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id']
                ])->execute();
            } else {
                // if session is exist but event!=stopped , we should continue the old session
                container()->get('dbal')->prepare("UPDATE `peers` SET `agent`=:agent, `ip` = :ip, `port` = :port,
                   `endpoints` = :endpoints, `connect_type` = :connect_type,
                   `seeder`=:seeder, `uploaded`=`uploaded` + :uploaded, `downloaded`= `downloaded` + :download, `to_go` = :left,
                   `last_action_at`= FROM_UNIXTIME(:last_action_at), `corrupt`= :corrupt, `key`= :key
                    WHERE `user_id` = :uid AND `torrent_id` = :tid AND `peer_id`=:pid")->bindParams([
                    'agent' => $queries['user-agent'],
                    'ip' => $queries['ip'], 'port' => $queries['port'],
                    'endpoints' => json_encode($queries['endpoints']), 'connect_type' => $queries['connect_type'],
                    'seeder' => $seeder,
                    'uploaded' => $trueUploaded, 'download' => $trueDownloaded, 'left' => $queries['left'],
                    'last_action_at' => $timenow,
                    'corrupt' => $queries['corrupt'], 'key' => $queries['key'],
                    'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'], 'pid' => $queries['peer_id']
                ])->execute();
            }
            if (container()->get('dbal')->getRowCount() > 0) {   // It means that the delete or update query affected so we can safety update `snatched` table
                container()->get('dbal')->prepare("UPDATE `snatched` SET `true_uploaded` = `true_uploaded` + :true_up,`true_downloaded` = `true_downloaded` + :true_dl,
                    `this_uploaded` = `this_uploaded` + :this_up, `this_download` = `this_download` + :this_dl, `to_go` = :left, `{$timeKey}`=`{$timeKey}` + :duration,
                    `ip` = INET6_ATON(:ip),`port` = :port, `agent` = :agent WHERE `torrent_id` = :tid AND `user_id` = :uid")->bindParams([
                    'true_up' => $trueUploaded, 'true_dl' => $trueDownloaded, 'this_up' => $thisUploaded, 'this_dl' => $thisDownloaded,
                    'left' => $queries['left'], 'duration' => $duration,
                    'ip' => $queries['ip'], 'port' => $queries['port'], 'agent' => $queries['user-agent'],
                    'tid' => $torrentInfo['id'], 'uid' => $userInfo['id']
                ])->execute();
            }
        }

        // Deal with completed event
        if ($queries['event'] === 'completed') {
            container()->get('dbal')->prepare("UPDATE `snatched` SET `finished` = 'yes', finish_ip = INET6_ATON(:ip), finish_at = NOW() WHERE user_id = :uid AND torrent_id = :tid")->bindParams([
                'ip' => $queries['ip'],
                'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'],
            ]);
            // Update `torrents`, with complete +1  incomplete -1 downloaded +1
            container()->get('dbal')->prepare('UPDATE `torrents` SET `complete` = `complete` + 1, `incomplete` = `incomplete` -1 , `downloaded` = `downloaded` + 1 WHERE `id`=:tid')->bindParams([
                'tid' => $torrentInfo['id']
            ])->execute();
        }

        // Update Table `users` , record his upload and download data and connect time information
        container()->get('dbal')->prepare('UPDATE `users` SET uploaded = uploaded + :upload, downloaded = downloaded + :download, '
            . ($trueUploaded > 0 ? 'last_upload_at=NOW(), ' : '') . ($trueDownloaded > 0 ? 'last_download_at=NOW(), ' : '') .
            "`last_connect_at`=NOW() , `last_tracker_ip`= INET6_ATON(:ip) WHERE id = :uid")->bindParams([
            'upload' => $thisUploaded, 'download' => $thisDownloaded,
            'uid' => $userInfo['id'], 'ip' => $queries['ip']
        ])->execute();
    }

    /** Cheater check function from NexusPHP based on user upload speed check
     *
     * See raw code from : https://github.com/ZJUT/NexusPHP/blob/master/include/functions_announce.php#L76
     *
     * @param $userInfo
     * @param $torrentInfo
     * @param $trueUploaded
     * @param $trueDownloaded
     * @param $duration
     * @param $upspeed
     */
    private function checkUpspeed($userInfo, $torrentInfo, $trueUploaded, $trueDownloaded, $duration, $upspeed)
    {
        $logCheater = function ($commit) use ($userInfo, $torrentInfo, $trueUploaded, $trueDownloaded, $duration) {
            container()->get('dbal')->prepare("INSERT INTO `cheaters`(`added_at`, `userid`, `torrentid`, `uploaded`, `downloaded`, `anctime`, `seeders`, `leechers`, `hit`, `commit`, `reviewed`, `reviewed_by`)
            VALUES (CURRENT_TIMESTAMP, :uid, :tid, :uploaded, :downloaded, :anctime, :seeders, :leechers, :hit, :msg, :reviewed, :reviewed_by)
            ON DUPLICATE KEY UPDATE `hit` = `hit` + 1, `reviewed` = 0,`reviewed_by` = '',`commit` = VALUES(`commit`)")->bindParams([
                'uid' => $userInfo['id'], 'tid' => $torrentInfo['id'],
                'uploaded' => $trueUploaded, 'downloaded' => $trueDownloaded, 'anctime' => $duration,
                'seeders' => $torrentInfo['complete'], 'leechers' => $torrentInfo['incomplete'],
                'hit' => 1, 'msg' => $commit,
                'reviewed' => 0, 'reviewed_by' => ''
            ])->execute();
        };

        // Uploaded more than 1 GB with uploading rate higher than 100 MByte/S (For Consertive level). This is no doubt cheating.
        if ($trueUploaded > 1 * (1024 ** 3) && $upspeed > 100 * (1024 ** 2)) {
            $logCheater('User account was automatically disabled by system');
            // Disable users and Delete user content in cache , so that user cannot get any data when next announce.
            container()->get('dbal')->prepare("UPDATE `users` SET `status` = 'banned' WHERE `id` = :uid;")->bindParams([
                'uid' => $userInfo['id'],
            ])->execute();

            container()->get('redis')->del(Constant::userBaseContentByPasskey($userInfo['passkey']));
        }

        // Uploaded more than 1 GB with uploading rate higher than 25 MByte/S (For Consertive level). This is likely cheating.
        if ($trueUploaded > 1 * (1024 ** 3) && $upspeed > 25 * (1024 ** 2)) {
            $logCheater('Abnormally high uploading rate');
        }

        // Uploaded more than 1 GB with uploading rate higher than 1 MByte/S when there is less than 8 leechers (For Consertive level). This is likely cheating.
        if ($trueUploaded > 1 * (1024 ** 3) && $upspeed > 1 * (1024 ** 2)) {
            $logCheater('User is uploading fast when there is few leechers');
        }

        //Uploaded more than 10 MB with uploading speed faster than 100 KByte/S when there is no leecher. This is likely cheating.
        if ($trueUploaded > 10 * (1024 ** 2) && $upspeed > 100 * 1024 && $torrentInfo['incomplete'] <= 0) {
            $logCheater('User is uploading when there is no leecher');
        }
    }

    private function getTorrentBuff($userid, $torrentid, $trueUploaded, $trueDownloaded, $upspeed, &$thisUploaded, &$thisDownloaded)
    {
        $buff = container()->get('redis')->get('TRACKER:buff:user_' . $userid . ':torrent_' . $torrentid);
        if ($buff === false) {
            $buff = container()->get('dbal')->prepare("SELECT COALESCE(MAX(`upload_ratio`),1) as `up_ratio`, COALESCE(MIN(`download_ratio`),1) as `dl_ratio` FROM `torrent_buffs`
            WHERE start_at < NOW() AND NOW() < expired_at AND (torrent_id = :tid OR torrent_id = 0) AND (beneficiary_id = :bid OR beneficiary_id = 0);")->bindParams([
                'tid' => $torrentid, 'bid' => $userid
            ])->fetchOne();
            container()->get('redis')->setex('TRACKER:buff:user_' . $userid . ':torrent_' . $torrentid, (int)config('tracker.interval'), $buff);
        }
        $thisUploaded = (int)($trueUploaded * ($buff['up_ratio'] ?: 1));
        $thisDownloaded = (int)($trueDownloaded * ($buff['dl_ratio'] ?: 1));
    }
}
