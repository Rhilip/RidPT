<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/7/2019
 * Time: 11:29 PM
 */

namespace App\Models\Form\Traits;

use App\Libraries\Constant;
use Redis;

trait actionRateLimitCheckTrait
{
    protected static function getRateLimitRules(): array
    {
        /** @noinspection PhpUnusedLocalVariableInspection */
        $pool = 'user_' . app()->auth->getCurUser()->getId();
        return [
            /* ['key' => 'dl_60', 'period' => 60, 'max' => 5, 'pool' => $pool] */
        ];
    }

    private function isRateLimitHit($limit_status)
    {
        $pool = $limit_status['pool'] ?? 'default';
        $action = $limit_status['key'] ?? 'default';
        $key = Constant::rateLimitPool($pool, $action);

        $period = $limit_status['period'] ?? 60;

        $now_ts = time();
        $pipe = app()->redis->multi(Redis::PIPELINE);

        $pipe->zAdd($key, $now_ts, $now_ts);
        $pipe->zRemRangeByScore($key, 0, $now_ts - $period);
        $pipe->zCard($key);
        $pipe->expire($key, $period + 1);

        $replies = $pipe->exec();
        $count = $replies[2];
        return [$count <= ($limit_status['max'] ?? 10), $count];
    }

    protected function hookRateLimitCheckFailed($limit_status, $count)
    {
    }

    /** @noinspection PhpUnused */
    protected function rateLimitCheck()
    {
        if (empty($this::getRateLimitRules())) {
            return;
        }  // It seems we don't need rate limit

        foreach ($this::getRateLimitRules() as $limit_status) {
            list($vary, $count) = $this->isRateLimitHit($limit_status);

            if (false === $vary) {
                $this->hookRateLimitCheckFailed($limit_status, $count);
                $this->buildCallbackFailMsg('rate', 'rate limit hit');
                return;
            }
        }
    }
}
