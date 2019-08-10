<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 11:29 PM
 */

namespace apps\models\form\Traits;


use apps\libraries\Constant;
use Redis;

trait actionRateLimitCheckTrait
{

    protected function getRateLimitRules(): array
    {
        return [
            /* ['key' => 'dl_60', 'period' => 60, 'max' => 5] */
        ];
    }

    private function isRateLimitHit($action_key, $period, $max_count, $pool = null): bool
    {
        $pool = $pool ?? 'user_' . app()->site->getCurUser()->getId();
        $key = Constant::rateLimitPool($pool, $action_key);
        $now_ts = time() * 1000;
        $pipe = app()->redis->multi(Redis::PIPELINE);

        $pipe->zAdd($key, $now_ts, $now_ts);
        $pipe->zRemRangeByScore($key, 0, $now_ts - $period * 1000);
        $pipe->zCard($key);
        $pipe->expire($key, $period + 1);

        $replies = $pipe->exec();
        $count = $replies[2];
        return $count <= $max_count;
    }

    protected function rateLimitCheck()
    {
        if (empty($this->getRateLimitRules())) return;  // It seems we don't need rate limit

        foreach ($this->getRateLimitRules() as $limit_status) {
            if (!$this->isRateLimitHit($limit_status['key'], $limit_status['period'], $limit_status['max'])) {
                $this->buildCallbackFailMsg('rate', 'rate limit hit');
                return;
            }
        }
    }
}
