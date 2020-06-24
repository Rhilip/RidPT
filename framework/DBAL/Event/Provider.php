<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/8/2020
 * Time: 8:51 PM
 */

declare(strict_types=1);

namespace Rid\DBAL\Event;

use Rid\DBAL\Event\Listener\ExecuteListener;
use Rid\Swoole\Helper\ServerHelper;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;

class Provider implements ListenerProviderInterface
{
    protected ExecuteListener $executeListener;

    public function __construct(ExecuteListener $executeListener)
    {
        $this->executeListener = $executeListener;
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        if (!ServerHelper::getServer()->taskworker) {
            $listenerAcceptor->addListener('dbal.execute', $this->executeListener);
        }
    }
}
