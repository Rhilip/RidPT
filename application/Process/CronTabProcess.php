<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/17
 * Time: 22:19
 */

namespace App\Process;

use Ahc\Cron\Expression;
use App\Libraries\Bonus;
use App\Libraries\Constant;


use Rid\Helpers\IoHelper;
use Rid\Swoole\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

final class CronTabProcess extends Process
{
    private bool $_print_flag = true;  // FIXME debug model on

    /**
     * @var Stopwatch
     */
    private ?Stopwatch $stopwatch = null;
    /**
     * @var Expression
     */
    private ?Expression $expr = null;

    private ?array $jobs = [];

    public function init()
    {
        $this->_print_flag = $this->_print_flag ?? config('debug.print_crontab_log');
        $this->stopwatch = new Stopwatch();
        $this->expr = new Expression();
        $this->jobs = container()->get('pdo')->prepare('SELECT * FROM `site_crontab` WHERE `priority` > 0 ORDER BY priority;')->queryAll();
    }

    private function print_log($log)
    {
        if ($this->_print_flag) {
            IoHelper::getIo()->text($log);
        }
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $hit = 0;
        $this->stopwatch->start('cron_all');
        foreach ($this->jobs as $index => $job) {
            if (!$this->expr->isCronDue($job['expr'], $job['last_run_at'])) {
                continue;
            };

            if (method_exists($this, $job['job'])) {
                $hit++;
                $this->stopwatch->start('cron_' . $job['job']);

                container()->get('pdo')->beginTransaction();
                $this->print_log('CronTab Worker Start To run job : ' . $job['job']);
                try {
                    // Run this job
                    $this->{$job['job']}($job);

                    container()->get('pdo')->commit(); // Finish The Transaction and commit~
                } catch (\Exception $e) {
                    container()->get('pdo')->rollback();
                    container()->get('logger')->critical('The run job throw Exception : ' . $e->getMessage());
                }

                $job_event = $this->stopwatch->stop('cron_' . $job['job']);

                // Update the run information
                $last_run_at = time();
                $this->jobs[$index]['last_run_at'] = $last_run_at;
                container()->get('pdo')->prepare('UPDATE `site_crontab` set last_run_at = FROM_UNIXTIME(:last_run_at) WHERE id=:id')->bindParams([
                    'id' => $job['id'], 'last_run_at' => $last_run_at
                ])->execute();
                $this->print_log('The run job : ' . $job['job'] . ' Finished. ' . 'Cost: ' . (string)$job_event . '.');
            } else {
                container()->get('logger')->critical('CronTab Worker Tries to run a none-exist job:' . $job['job']);
                unset($this->jobs[$index]);
            }
        }
        $event = $this->stopwatch->stop('cron_all');
        if ($hit > 0) {
            $this->print_log('This Cron Work period Start At ' . $event->getOrigin() . ', Cost: ' . (string)$event . ', With ' . $hit . ' Jobs hits.');
        }
    }

    /** @noinspection PhpUnused */
    protected function clean_expired_zset_cache()
    {
        $timenow = time();

        $clean_list = [
            // Lock
            [Constant::trackerAnnounceLockZset, 'Success Clean %s tracker announce locks.'],
            [Constant::trackerAnnounceMinIntervalLockZset, 'Success Clean %s tracker min announce interval locks.'],

            // Valid Zset
            [Constant::trackerValidClientZset, 'Success Clean %s valid bittorrent client.'],
            [Constant::trackerValidPeerZset, 'Success Clean %s valid peers.']
        ];

        foreach ($clean_list as $item) {
            [$field, $msg] = $item;
            $clean_count = container()->get('redis')->zRemRangeByScore($field, 0, $timenow);
            if ($clean_count > 0) {
                $this->print_log(sprintf($msg, $clean_count));
            }
        }
    }

    /** @noinspection PhpUnused */
    protected function clean_dead_peer()
    {
        $deadtime = floor(config('tracker.interval') * 1.8);
        container()->get('pdo')->prepare('DELETE FROM `peers` WHERE last_action_at < DATE_SUB(NOW(), interval :deadtime second )')->bindParams([
            'deadtime' => $deadtime
        ])->execute();
        $affect_peer_count = container()->get('pdo')->getRowCount();
        $this->print_log('Success clean ' . $affect_peer_count . ' peers from our peer list');
    }

    /** @noinspection PhpUnused */
    protected function clean_expired_items_database()
    {
        $clean_sqls = [
            [  // expired session
                'UPDATE `sessions` SET `expired` = 1 WHERE `expired` = 0 AND `login_at` < DATE_SUB(NOW(), INTERVAL 15 MINUTE)',
                'Success clean %s expired sessions'
            ],
            [  // expired invitee
                'UPDATE `invite` SET `used` = -1 WHERE `used` = 0 AND `expire_at` < NOW()',
                'Success clean %s expired invites'
            ]
        ];

        foreach ($clean_sqls as $item) {
            [$clean_sql, $msg] = $item;
            container()->get('pdo')->prepare($clean_sql)->execute();
            $clean_count = container()->get('pdo')->getRowCount();
            if ($clean_count > 0) {
                $this->print_log(sprintf($msg, $clean_count));
            }
        }
    }

    protected function calculate_seeding_bonus() // TODO
    {
        $calculate = new Bonus();
        $seeders = container()->get('pdo')->prepare("SELECT DISTINCT user_id FROM peers WHERE seeder = 'yes'")->queryColumn();

        foreach ($seeders as $seeder) {
            $bonus = $calculate->calculate($seeder);
            container()->get('site')->addBonus($seeder, $bonus, '+', 'seeding');
        }
    }

    /**
     * sync torrents status about complete, incomplete, comments
     * @noinspection PhpUnused
     */
    protected function sync_torrents_status()
    {
        $torrents_update = [];

        $wrong_complete_records = container()->get('pdo')->prepare("
            SELECT torrents.`id`, `complete` AS `record`, COUNT(`peers`.id) AS `real` FROM `torrents`
              LEFT JOIN peers ON `peers`.torrent_id = `torrents`.id AND `peers`.`seeder` = 'yes'
            GROUP BY torrents.`id` HAVING `record` != `real`;")->queryAll();
        if ($wrong_complete_records) {
            foreach ($wrong_complete_records as $arr) {
                $torrents_update[$arr['id']]['complete'] = $arr['real'];
            }
        }
        $wrong_incomplete_records = container()->get('pdo')->prepare("
            SELECT torrents.`id`, `incomplete` AS `record`, COUNT(`peers`.id) AS `real` FROM `torrents`
              LEFT JOIN peers ON `peers`.torrent_id = `torrents`.id AND (`peers`.`seeder` = 'partial' OR `peers`.`seeder` = 'no')
            GROUP BY torrents.`id` HAVING `record` != `real`;")->queryAll();
        if ($wrong_incomplete_records) {
            foreach ($wrong_incomplete_records as $arr) {
                $torrents_update[$arr['id']]['incomplete'] = $arr['real'];
            }
        }

        $wrong_comment_records = container()->get('pdo')->prepare('
            SELECT t.id, t.comments as `record`, COUNT(tc.id) as `real` FROM torrents t
              LEFT JOIN torrent_comments tc on t.id = tc.torrent_id
            GROUP BY t.id HAVING `record` != `real`')->queryAll();
        if ($wrong_comment_records) {
            foreach ($wrong_incomplete_records as $arr) {
                $torrents_update[$arr['id']]['comments'] = $arr['real'];
            }
        }

        if ($torrents_update) {
            foreach ($torrents_update as $tid => $update) {
                container()->get('pdo')->update('torrents', $update, [['id', '=', $tid]])->execute();
                container()->get('redis')->del(Constant::torrentContent($tid));
            }
            $this->print_log('Fix ' . count($torrents_update) . ' wrong torrents records about complete, incomplete, comments.');
        }
    }

    /** @noinspection PhpUnused */
    protected function sync_ban_list()
    {
        // Sync Banned Emails list
        $ban_email_list = container()->get('pdo')->prepare('SELECT `email` from `ban_emails`')->queryColumn() ?: [];
        container()->get('redis')->sAddArray(Constant::siteBannedEmailSet, $ban_email_list);

        // Sync Banned Username list
        $ban_username_list = container()->get('pdo')->prepare('SELECT `username` from `ban_usernames`')->queryColumn() ?: [];
        container()->get('redis')->sAddArray(Constant::siteBannedUsernameSet, $ban_username_list);
    }

    protected function update_expired_external_link_info()
    {
        $expired_links_res = container()->get('pdo')->prepare('SELECT `source`,`sid` FROM `external_info` ORDER BY `update_at` ASC LIMIt 5')->queryAll();
        if ($expired_links_res !== false) {
            foreach ($expired_links_res as $link_res) {
                $source = $link_res['source'];
                $sid = $link_res['sid'];
                // TODO Pt-Gen
            }
        }
    }
}
