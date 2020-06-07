<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 10:48 PM
 */

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Libraries\Constant;

trait actionRateLimitCheckTrait
{

    /**
     * [
     *    ['key' => 'dl_60', 'period' => 60, 'max' => 5, 'pool' => $pool]
     * ]
     *
     * @return array
     */
    abstract public function getRateLimitRules(): array;

    protected function actionRateLimitCheck()
    {
        foreach ($this->getRateLimitRules() as $rule) {
            list($vary, $count) = $this->isRateLimitHit($rule);

            if (false === $vary) {
                /** @noinspection PhpUndefinedMethodInspection */
                $this->buildCallbackFailMsg('rate', 'rate limit hit');
                return;
            }
        }
    }

    private function isRateLimitHit($limit_status)
    {
        $pool = $limit_status['pool'] ?? 'default';
        $action = $limit_status['key'] ?? 'default';
        $key = 'RateLimit:' . $pool . ':action_' . $action;

        $period = $limit_status['period'] ?? 60;

        $now_ts = time();
        $pipe = container()->get('redis')->multi(\Redis::PIPELINE);

        /** @noinspection PhpParamsInspection */
        $pipe->zAdd($key, $now_ts, $now_ts);
        $pipe->zRemRangeByScore($key, 0, $now_ts - $period);
        $pipe->zCard($key);
        $pipe->expire($key, $period + 1);

        $replies = $pipe->exec();
        $count = $replies[2];
        return [$count <= ($limit_status['max'] ?? 10), $count];
    }
}
