<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/17
 * Time: 22:19
 */

namespace apps\task;

use Rid\Base\Timer;

class CronTabTimer extends Timer
{
    
    private $_print_flag = 1;  // FIXME debug model on
    
    private function print_log($log)
    {
        $this->_print_flag = $this->_print_flag ?? app()->config->get('debug.print_crontab_log');
        if ($this->_print_flag) {
            println($log);
        }
    }

    /**
     * @throws \Exception
     */
    public function init()
    {
        // Get all run
        $to_run_jobs = app()->pdo->createCommand('SELECT * FROM `site_crontab` WHERE `priority` > 0 AND `next_run_at` < NOW() ORDER BY priority ASC;')->queryAll();
        foreach ($to_run_jobs as $job) {
            if (method_exists($this, $job['job'])) {
                app()->pdo->beginTransaction();
                $this->print_log('CronTab Worker Start To run job : ' . $job['job']);
                try {
                    // Run this job
                    $start_time = time();
                    $this->{$job['job']}($job);
                    $end_time = time();

                    // Update the run information
                    app()->pdo->createCommand('UPDATE `site_crontab` set last_run_at= NOW() , next_run_at= DATE_ADD(NOW(), interval job_interval second) WHERE id=:id')->bindParams([
                        'id' => $job['id']
                    ])->queryOne();
                    $next_run_at = app()->pdo->createCommand('SELECT `next_run_at` FROM `site_crontab` WHERE id=:id')->bindParams([
                        'id' => $job['id']
                    ])->queryScalar();   // FIXME Bad Code
                    $this->print_log('The run job : ' . $job['job'] . ' Finished. ' .
                        'Cost time: ' . number_format($end_time - $start_time, 10) . 's, ' . 'Next run at : ' . $next_run_at
                    );

                    // Finish The Transaction and commit~
                    app()->pdo->commit();
                } catch (\Exception $e) {
                    app()->pdo->rollback();
                    app()->log->critical('The run job throw Exception : ' . $e->getMessage());
                    if (env('APP_DEBUG')) throw $e;
                }
            } else {
                app()->log->critical('CronTab Worker Tries to run a none-exist job:' . $job['job']);
            }
        }
    }

    protected function clean_dead_peer()
    {
        $deadtime = floor(app()->config->get('tracker.interval') * 1.8);
        app()->pdo->createCommand('DELETE FROM `peers` WHERE last_action_at < DATE_SUB(NOW(), interval :deadtime second )')->bindParams([
            'deadtime' => $deadtime
        ])->execute();
        $affect_peer_count = app()->pdo->getRowCount();
        $this->print_log('Success clean ' . $affect_peer_count . ' peers from our peer list');
    }

    protected function clean_expired_session() {
        $timenow = time();

        $expired_sessions = app()->redis->zRangeByScore('Site:Sessions:to_expire', 0, $timenow);
        foreach ($expired_sessions as $session) {
            app()->pdo->createCommand('UPDATE `users_session_log` SET `expired` = 1 WHERE sid = :sid')->createCommand([
                'sid' => $session
            ])->execute();
        }

        $clean_record_count = app()->redis->zRemRangeByScore('Site:Sessions:to_expire', 0, $timenow);
        $this->print_log('Success clean expired Sessions: Database(' . count($expired_sessions) .'), Redis(' . $clean_record_count .').');
    }

    // TODO sync sessions from database to redis to avoid lost (Maybe)...
}
