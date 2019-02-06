<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/6
 * Time: 20:25
 */

namespace Rid\Config;

use Rid\Base\Component;
use Rid\Exceptions\ConfigException;

class ConfigByRedis extends Component implements DynamicConfigInterface
{

    /** Config key prefix in Cache
     * @var string
     */
    public $cacheField = "CONFIG:site_config";

    public $cacheExpire = 86400;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        if (!app()->redis->exists($this->cacheField)) {
            $configs_pdo = app()->pdo->createCommand("SELECT `name`,`value` FROM  `site_config`")->queryAll();
            $configs = array_column($configs_pdo, 'value', 'name');
            app()->redis->hMset($this->cacheField, $configs);
            app()->redis->expire($this->cacheField, $this->cacheExpire);
        }
    }

    public function get(string $name, bool $throw = true)
    {
        // First Check config stored in RedisConnection Cache, If it exist , then just return the cached key
        $setting = app()->redis->hget($this->cacheField, $name);
        if (!is_null($setting)) return $setting;
        // Get config From Database
        $setting = app()->pdo->createCommand("SELECT `value` from `site_config` WHERE `name` = :name")
            ->bindParams(["name" => $name])->queryScalar();

        // In this case (Load config From Database Failed) , A Exception should throw
        if ($setting === false)
            throw new ConfigException(sprintf("Dynamic Setting \"%s\" couldn't be found.", $name));

        // Cache it in RedisConnection and return
        app()->redis->hset($this->cacheField, $name, $setting);
        return $setting;
    }

    public function getAll()
    {
        return app()->redis->hgetall($this->cacheField);
    }

    public function getSection($prefix = null)
    {
        return array_filter($this->getAll(), function ($k) use ($prefix) {
            return strpos($k, $prefix) === 0;
        }, ARRAY_FILTER_USE_KEY);
    }

    public function set(string $name, $value)
    {
        app()->pdo->createCommand("UPDATE `site_config` SET `value` = :val WHERE `name` = :name")->bindParams([
            "val" => $value, "name" => $name
        ])->execute();
        return $this->flush($name);
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value)
            $this->set($key, $value);
    }

    public function flush($name)
    {
        app()->redis->hdel($this->cacheField, $name);
        return $this->get($name);
    }
}
