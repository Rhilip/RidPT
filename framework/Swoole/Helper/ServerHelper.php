<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Helper;

use Swoole\Server;

class ServerHelper
{
    protected static ?Server $server = null;

    /**
     * @return Server|null
     */
    public static function getServer(): ?Server
    {
        return self::$server;
    }

    /**
     * @param Server|null $server
     */
    public static function setServer(?Server $server): void
    {
        self::$server = $server;
    }
}
