<?php

namespace Rid;

use Rid\Base\Application;

/**
 * Rid类
 */
class Rid
{
    // App实例
    protected static Application $_app;

    /**
     * @return Application
     */
    public static function getApp(): Application
    {
        return self::$_app;
    }

    /**
     * @param Application $app
     */
    public static function setApp(Application $app): void
    {
        self::$_app = $app;
    }
}
