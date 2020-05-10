<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/10/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Event\Provider;

use App\Event\Listener\PDOListener;
use App\Event\Listener\RedisListener;

use League\Event\ListenerAcceptorInterface;
use League\Event\ListenerProviderInterface;
use Rid\Swoole\Helper\ServerHelper;

class RuntimeProvider implements ListenerProviderInterface
{
    protected PDOListener $PDOListener;
    protected RedisListener $redisListener;

    public function __construct(
        PDOListener $PDOListener,
        RedisListener $redisListener
    ) {
        $this->PDOListener = $PDOListener;
        $this->redisListener = $redisListener;
    }

    public function provideListeners(ListenerAcceptorInterface $listenerAcceptor)
    {
        if (!ServerHelper::getServer()->taskworker) {
            $listenerAcceptor->addListener('database.commit', $this->PDOListener);
            $listenerAcceptor->addListener('redis.command.call', $this->redisListener);
        }
    }
}
