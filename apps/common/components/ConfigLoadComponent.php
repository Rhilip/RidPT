<?php
/**
 * Created by PhpStorm.
 * User: Rhili
 * Date: 2018/11/21
 * Time: 20:39
 */

namespace apps\common\components;

use mix\facades\PDO;
use mix\facades\Redis;

use mix\base\Component;

class ConfigLoadComponent extends Component
{

    /** Config key prefix in Cache
     * @var string
     */
    public $saveKeyPrefix = "CONFIG:";

    /** Config key stored table in Database
     * @var string
     */
    public $table = "site_config";

    public function get(string $name)
    {
        $cache_key = $this->buildCacheKey($name);

        // First Check config stored in Redis Cache, If it exist , then just return the cached key
        $setting = Redis::get($cache_key);
        if ($setting !== false) return $setting;

        // Get config From Database
        $setting = PDO::createCommand("SELECT `value` from `{$this->table}` WHERE `name` = :name")
            ->bindParams(["name" => $name])->queryScalar();

        // In this case (Load config From Database Failed) , A Exception should throw
        if ($setting === false) throw $this->createNotFoundException($name);

        // Cache it in Redis and return
        Redis::setex($cache_key, 86400, $setting);
        return $setting;
    }

    public function set(string $name, $value)
    {
        PDO::update($this->table, ['value' => $value], [['name', '=', $name]])->execute();
        $this->flush($name);
    }

    public function flush($name)
    {
        Redis::del($this->buildCacheKey($name));
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value)
            $this->set($key, $value);
    }

    protected function buildCacheKey($key): string
    {
        return $this->saveKeyPrefix . $key;
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
