<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Helper;

class Server
{
    protected static ?\Swoole\Server $server = null;

    /**
     * @return \Swoole\Server|null
     */
    public static function getServer(): ?\Swoole\Server
    {
        return self::$server;
    }

    /**
     * @param \Swoole\Server|null $server
     */
    public static function setServer(?\Swoole\Server $server): void
    {
        self::$server = $server;
    }
}
