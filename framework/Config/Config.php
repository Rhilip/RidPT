<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/21
 * Time: 20:39
 */

namespace Mix\Config;

use Mix\Base\Component;
use Mix\Exceptions\ConfigException;

class Config extends Component
{
    /** @var \swoole_table */
    private $cacheTable;
    private $valueField = 'data';

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->cacheTable = new \Swoole\Table(2048);

        $this->cacheTable->column($this->valueField, \Swoole\Table::TYPE_STRING, 256);
        $this->cacheTable->create();

        $configs = app()->pdo->createCommand("SELECT `name`,`value` FROM  `site_config`")->queryAll();
        foreach ($configs as $config) {
            $this->cacheTable->set($config["name"], [$this->valueField => $config["value"]]);
        }
    }

    public function get(string $name)
    {
        $setting = $this->cacheTable->get($name, $this->valueField);
        // First Check config stored in RedisConnection Cache, If it exist , then just return the cached key
        if (false === $setting) {
            // Get config From Database
            $setting = app()->pdo->createCommand("SELECT `value` from `site_config` WHERE `name` = :name")
                ->bindParams(["name" => $name])->queryScalar();
            // In this case (Load config From Database Failed) , A Exception should throw
            if ($setting === false) throw $this->createNotFoundException($name);

            $this->cacheTable->set($name, [$this->valueField => $setting]);
        }
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
        $this->cacheTable->del($name);
        return $this->get($name);
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value)
            $this->set($key, $value);
    }

    /**
     * @param string $name Name of the setting.
     * @return ConfigException
     */
    protected function createNotFoundException($name)
    {
        return new ConfigException(sprintf("Dynamic Setting \"%s\" couldn't be found.", $name));
    }

}
