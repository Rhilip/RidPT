<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/8/2020
 * Time: 9:03 PM
 */

declare(strict_types=1);

namespace Rid\Redis\Event;


use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use Rid\Redis\Event\Listener\CallListener;
use Rid\Swoole\Helper\ServerHelper;

class Provider implements ListenerProviderInterface
{
    private CallListener $callListener;

    public function __construct(CallListener $callListener)
    {
        $this->callListener = $callListener;
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        if (!ServerHelper::getServer()->taskworker) {
            $listenerAcceptor->addListener('redis.call', $this->callListener);
        }
    }
}
