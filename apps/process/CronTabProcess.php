<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/17
 * Time: 22:19
 */

namespace apps\process;

use apps\libraries\Constant;
use Rid\Base\Process;


final class CronTabProcess extends Process
{

    private $_print_flag = 1;  // FIXME debug model on

    private $_none_exist_job = [];

    private function print_log($log)
    {
        $this->_print_flag = $this->_print_flag ?? config('debug.print_crontab_log');
        if ($this->_print_flag) {
            println($log);
        }
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        // Get all run
        $to_run_jobs = app()->pdo->createCommand('SELECT * FROM `site_crontab` WHERE `priority` > 0 AND `next_run_at` < NOW() ORDER BY priority ASC;')->queryAll();

        $hit = 0;
        $start_time = time();
        foreach ($to_run_jobs as $job) {
            if (method_exists($this, $job['job'])) {
                app()->pdo->beginTransaction();
                $this->print_log('CronTab Worker Start To run job : ' . $job['job']);
                try {
                    // Run this job
                    $job_start_time = time();
                    $this->{$job['job']}($job);
                    $job_end_time = time();
                    $hit++;

                    // Update the run information
                    $next_run_at = $job_end_time + $job['job_interval'];
                    app()->pdo->createCommand('UPDATE `site_crontab` set last_run_at = FROM_UNIXTIME(:last_run_at) , next_run_at = FROM_UNIXTIME(:next_run_at) WHERE id=:id')->bindParams([
                        'id' => $job['id'], 'last_run_at' => $job_end_time, 'next_run_at' => $next_run_at
                    ])->execute();
                    $this->print_log('The run job : ' . $job['job'] . ' Finished. ' .
                        'Cost time: ' . number_format($job_end_time - $job_start_time, 10) . 's, ' . 'Next run at : ' . $next_run_at
                    );

                    // Finish The Transaction and commit~
                    app()->pdo->commit();
                } catch (\Exception $e) {
                    app()->pdo->rollback();
                    app()->log->critical('The run job throw Exception : ' . $e->getMessage());
                    if (env('APP_DEBUG')) throw $e;
                }
            } else {
                if (!in_array($job, $this->_none_exist_job)) {
                    $this->_none_exist_job[] = $job;
                    app()->log->critical('CronTab Worker Tries to run a none-exist job:' . $job['job']);
                }
            }
        }
        $end_time = time();
        if ($hit > 0) $this->print_log('This Cron Work period Start At ' . $start_time . ', Cost Time: ' . number_format($end_time - $start_time, 10) . 's, With ' . $hit . ' Jobs hits.');
    }

    protected function clean_expired_zset_cache() {
        $timenow = time();

        $clean_list = [
            // Lock
            [Constant::trackerAnnounceLockZset, 'Success Clean %s tracker announce locks.'],
            [Constant::trackerAnnounceMinIntervalLockZset, 'Success Clean %s tracker min announce interval locks.'],

            // Invalid Zset
            [Constant::invalidUserIdZset, 'Success Clean %s invalid user id.'],
            [Constant::invalidUserSessionZset, 'Success Clean %s invalid user session.'],
            [Constant::invalidUserPasskeyZset, 'Success Clean %s invalid user passkey.'],
            [Constant::trackerInvalidInfoHashZset, 'Success Clean %s invalid info_hash.'],

            // Valid Zset
            [Constant::trackerValidClientZset, 'Success Clean %s valid bittorrent client.'],
            [Constant::trackerValidPeerZset, 'Success Clean %s valid peers.']
        ];

        foreach ($clean_list as $item) {
            [$field, $msg] = $item;
            $clean_count = app()->redis->zRemRangeByScore($field, 0, $timenow);
            if ($clean_count > 0) $this->print_log(sprintf($msg, $clean_count));
        }
    }

    protected function clean_dead_peer()
    {
        $deadtime = floor(config('tracker.interval') * 1.8);
        app()->pdo->createCommand('DELETE FROM `peers` WHERE last_action_at < DATE_SUB(NOW(), interval :deadtime second )')->bindParams([
            'deadtime' => $deadtime
        ])->execute();
        $affect_peer_count = app()->pdo->getRowCount();
        $this->print_log('Success clean ' . $affect_peer_count . ' peers from our peer list');
    }

    protected function calculate_seeding_bonus()
    {
        // TODO
    }

    protected function clean_expired_session()
    {
        $timenow = time();

        $expired_sessions = app()->redis->zRangeByScore('Site:Sessions:to_expire', 0, $timenow);
        foreach ($expired_sessions as $session) {
            app()->pdo->createCommand('UPDATE `user_session_log` SET `expired` = 1 WHERE sid = :sid')->bindParams([
                'sid' => $session
            ])->execute();
        }

        $clean_record_count = app()->redis->zRemRangeByScore('Site:Sessions:to_expire', 0, $timenow);
        if ($clean_record_count > 0) $this->print_log('Success clean expired Sessions: Database(' . count($expired_sessions) . '), Redis(' . $clean_record_count . ').');
    }

    protected function expired_invitee()
    {
        app()->pdo->createCommand('UPDATE `invite` SET `used` = -1 WHERE `expire_at` < NOW() AND `used` = 0')->execute();

        $count = app()->pdo->getRowCount();
        $this->print_log('Success Expired ' . $count . ' invites');
    }

    // sync torrents status about complete, incomplete, comments
    protected function sync_torrents_status()
    {
        $cur_peer_status = app()->pdo->createCommand('SELECT `torrent_id`, `seeder`, COUNT(`id`) as `c` FROM `peers` GROUP BY `torrent_id`,`seeder`')->queryAll();
        $cur_commit_status = app()->pdo->createCommand('SELECT `torrent_id`, COUNT(`id`) as `c` FROM `torrent_comments` GROUP BY `torrent_id`')->queryAll();
        $cur_torrent_status = app()->pdo->createCommand('SELECT `id`,`complete`,`incomplete`,`comments` FROM `torrents`')->queryAll();


    }


    protected function update_expired_external_link_info()
    {
        $expired_links_res = app()->pdo->createCommand('SELECT `source`,`sid` FROM `external_info` ORDER BY `update_at` ASC LIMIt 5')->queryAll();
        if ($expired_links_res !== false) {
            foreach ($expired_links_res as $link_res) {
                $source = $link_res['source'];
                $sid = $link_res['sid'];
                // TODO Pt-Gen
            }
        }
    }
}
