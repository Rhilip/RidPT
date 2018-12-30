<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/21
 * Time: 20:39
 */

namespace Mix\Config;

use Mix\Base\Component;

class Config extends Component
{

    /** Config key prefix in Cache
     * @var string
     */
    public $cacheField = "CONFIG:site_config";

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $configs = app()->pdo->createCommand("SELECT `name`,`value` FROM  `site_config`")->queryAll();
        foreach ($configs as $config) {
            app()->redis->hset($this->cacheField, $config["name"], $config["value"]);
        }
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

    public function get(string $name)
    {
        // First Check config stored in RedisConnection Cache, If it exist , then just return the cached key
        $setting = app()->redis->hget($this->cacheField, $name);
        if (!is_null($setting)) return $setting;

        // Get config From Database
        $setting = app()->pdo->createCommand("SELECT `value` from `site_config` WHERE `name` = :name")
            ->bindParams(["name" => $name])->queryScalar();

        // In this case (Load config From Database Failed) , A Exception should throw
        if ($setting === false) throw $this->createNotFoundException($name);

        // Cache it in RedisConnection and return
        app()->redis->hset($this->cacheField, $name, $setting);
        return $setting;
    }

    public function set(string $name, $value)
    {
        app()->pdo->createCommand("UPDATE `site_config` SET `value` = :val WHERE `name` = :name")->bindParams([
            "val" => $value, "name" => $name
        ])->execute();
        return $this->flush($name);
    }

    public function flush($name)
    {
        app()->redis->hdel($this->cacheField, $name);
        return $this->get($name);
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value)
            $this->set($key, $value);
    }

    /**
     * @param string $name Name of the setting.
     * @return \RuntimeException
     */
    protected function createNotFoundException($name)
    {
        return new \RuntimeException(sprintf("Setting \"%s\" couldn't be found.", $name));
    }

}
