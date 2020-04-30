<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Memory;

use Swoole\Atomic;

class AtomicManager
{
    private static bool $init = false;
    protected static array $atomics = [];

    protected static array $configs = [];

    public static function init($atomic_configs)
    {
        if (self::$init) {
            throw new \RuntimeException('AtomicManager can not repeated init.');
        }

        self::$configs = $atomic_configs;
        foreach ($atomic_configs as $name => $init_value) {
            $atomic = new Atomic($init_value);
            self::$atomics[$name] = $atomic;
        }
        self::$init = true;
    }

    public static function get($name): Atomic
    {
        if (array_key_exists($name, self::$atomics)) {
            return self::$atomics[$name];
        }
        throw new \RuntimeException('AtomicManager can\'t find Atomic ' . $name);
    }

    /**
     * @return array
     */
    public static function getConfigs(): array
    {
        return self::$configs;
    }
}
