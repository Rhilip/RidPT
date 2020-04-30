<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/30/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Swoole\Table;

use Swoole\Table;

class TableManager
{
    private static bool $init = false;

    protected static array $table_configs = [];
    protected static array $tables = [];

    public static function init($table_configs)
    {
        if (self::$init) {
            throw new \RuntimeException('TableManager can not repeated init.');
        }

        self::$table_configs = $table_configs;
        foreach ($table_configs as $name => $config) {
            $table = new Table($config['size'], $config['conflict_proportion'] ?? 0.2);
            foreach ($config['columns'] as $column) {
                $table->column(...$column);
            }
            if (!$table->create()) {
                throw new \RuntimeException('TableManager create table failed.');
            }
            self::$tables[$name] = $table;
        }
        self::$init = true;
    }

    /**
     * @param $name
     * @return Table
     */
    public static function get($name): Table
    {
        if (array_key_exists($name, self::$tables)) {
            return self::$tables[$name];
        }
        throw new \RuntimeException('TableManager can\'t find Table ' . $name);
    }

    /**
     * @return array
     */
    public static function getTableConfigs(): array
    {
        return self::$table_configs;
    }
}
