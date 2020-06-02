<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Event\Listener;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class RedisListener extends AbstractListener
{
    public function handle(EventInterface $event, $params = null)
    {
        context()->append('record.redis', $this->flattenRedisCommands($params));
    }

    private function flattenRedisCommands($params)
    {
        $return = array();
        array_walk_recursive($params, function ($a) use (&$return) {
            $return[] = $a;
        });
        return implode(' ', $return);
    }
}
