<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/8/2020
 * Time: 9:04 PM
 */

declare(strict_types=1);

namespace Rid\Redis\Event\Listener;


use League\Event\AbstractListener;
use League\Event\EventInterface;

class CallListener extends AbstractListener
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
