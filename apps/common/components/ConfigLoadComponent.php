<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/21
 * Time: 20:39
 */

namespace apps\common\components;

use Mix\Facades\PDO;
use Mix\Facades\Redis;

use Mix\Base\Component;

class ConfigLoadComponent extends Component
{

    /** Config key prefix in Cache
     * @var string
     */
    public $saveField = "CONFIG:site_config";

    /** Config key stored table in Database
     * @var string
     */
    public $table = "site_config";

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $configs = PDO::createCommand("SELECT `name`,`value` FROM  `{$this->table}`")->queryAll();
        foreach($configs as $config) {
            Redis::hset($this->saveField,$config["name"],$config["value"]);
        }
    }

    public function getAll() {
        return Redis::hgetall($this->saveField);
    }

    public function get(string $name)
    {
        // First Check config stored in RedisConnection Cache, If it exist , then just return the cached key
        $setting = Redis::hget($this->saveField, $name);
        if (!is_null($setting)) return $setting;

        // Get config From Database
        $setting = PDO::createCommand("SELECT `value` from `{$this->table}` WHERE `name` = :name")
            ->bindParams(["name" => $name])->queryScalar();

        // In this case (Load config From Database Failed) , A Exception should throw
        if ($setting === false) throw $this->createNotFoundException($name);

        // Cache it in RedisConnection and return
        Redis::hset($this->saveField, $name, $setting);
        return $setting;
    }

    public function set(string $name, $value)
    {
        PDO::createCommand("UPDATE `{$this->table}` SET `value` = :val WHERE `name` = :name")->bindParams([
            "val" => $value, "name" => $name
        ])->execute();
        return $this->flush($name);
    }

    public function flush($name)
    {
        Redis::hdel($this->saveField, $name);
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
