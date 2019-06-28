<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/21
 * Time: 20:39
 */

namespace Rid\Component;

use Rid\Base\Component;
use Rid\Exceptions\ConfigException;

class Config extends Component
{
    /** @var \Swoole\Table */
    private $cacheTable;

    private $valueField = 'data';

    public function onInitialize(array $config = [])
    {
        // Get \Swoole\Table object From \Server, So that we can share same dynamic config
        $this->cacheTable = app()->getServ()->configTable;

        if ($this->cacheTable->count() == 0 && app()->getWorker() == 0) {
            $configs = app()->pdo->createCommand("SELECT `name`,`value` FROM  `site_config`")->queryAll();
            foreach ($configs as $config) {
                $this->cacheTable->set($config["name"], [$this->valueField => $config["value"]]);
            }
        }
    }

    public function get(string $name, bool $throw = true)
    {
        $setting = $this->cacheTable->get($name, $this->valueField);
        // First Check config stored in RedisConnection Cache, If it exist , then just return the cached key
        if (false === $setting) {
            // Get config From Database
            $setting = app()->pdo->createCommand("SELECT `value` from `site_config` WHERE `name` = :name")
                ->bindParams(["name" => $name])->queryScalar();
            // In this case (Load config From Database Failed) , A Exception should throw
            if ($setting === false && $throw)
                throw new ConfigException(sprintf("Dynamic Setting \"%s\" couldn't be found.", $name));

            $this->cacheTable->set($name, [$this->valueField => $setting]);
        }
        return $setting;
    }

    public function getAll()
    {
        $settings = [];
        foreach ($this->cacheTable as $k => $v) {
            $settings[$k] = $v[$this->valueField];
        }
        return $settings;
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

    public function flush($name)
    {
        $this->cacheTable->del($name);
        return $this->get($name);
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value)
            $this->set($key, $value);
    }
}
