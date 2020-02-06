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
use Swoole\Table;

class Config extends Component
{
    /** @var Table */
    private $cacheTable;

    public function onInitialize(array $config = [])
    {
        // Get \Swoole\Table object From \Server, So that we can share same dynamic config
        $this->cacheTable = app()->getServ()->configTable;

        if ($this->cacheTable->count() == 0 && app()->getServ()->worker_id == 0) {
            $configs = app()->pdo->prepare('SELECT `name`, `value`, `type` FROM `site_config`')->queryAll();
            foreach ($configs as $config) {
                $this->load($config);
            }
            println('Load Dynamic Site Config Success, Get ' . count($configs) . ' configs.');
        }
    }

    private function load($config)
    {
        $this->cacheTable->set($config['name'], ['value' => $config['value'], 'type' => $config['type']]);
    }

    public function get(string $name)
    {
        // First Check config stored in Swoole Table. If it exist , then just return the cached key
        if (false === $setting_row = $this->cacheTable->get($name)) {
            if (strpos($name, 'runtime.') === 0) {
                return false;
            } // Deal with config with prefix `runtime.`
            if (strpos($name, 'route.') === 0) {
                return 1;
            }       // Deal with config with prefix `route.`

            // Get config From Database
            $setting_row = app()->pdo->prepare('SELECT `name`, `value`, `type` from `site_config` WHERE `name` = :name')
                ->bindParams(['name' => $name])->queryOne();

            // In this case (Load config From Database Failed) , A Exception should throw
            if ($setting_row === false) {
                throw new ConfigException(sprintf('Dynamic Setting "%s" couldn\'t be found.', $name));
            }

            $this->load($setting_row);
        }

        $setting = $setting_row['value'];  // Type String
        if ($setting_row['type'] == 'json') {
            $setting = json_decode($setting, true);
        } elseif ($setting_row['type'] == 'int') {
            $setting = (int) $setting;
        } elseif ($setting_row['type'] == 'bool') {
            $setting = (bool) $setting;
        }

        return $setting;
    }

    public function getSection($prefix = null)
    {
        $settings = [];
        foreach ($this->cacheTable as $k => $v) {
            if (!is_null($prefix) && strpos($k, $prefix) !== 0) {
                continue;
            }
            $settings[$k] = $this->get($k);
        }
        return $settings;
    }

    public function set(string $name, $value, $type = null)
    {
        // Judge order: input -> pre-defined -> is_array so `json` -> default `string`
        $type = $type ?? ($this->cacheTable->get($name, 'type') ?: (is_array($value) ? 'json' : 'string'));
        $value = ($type == 'json') ? json_encode($value) : (string) $value;  // array(json), bool, int -> string

        $this->cacheTable->set($name, ['value' => $value, 'type' => $type]);
        // println(sprintf('Set new Dynamic Setting "%s", Type: "%s", Value: "%s".', $name, $type, $value));

        // Update site_config if not a runtime setting
        if (strpos($name, 'runtime.') === false) {
            app()->pdo->prepare('UPDATE `site_config` SET `value` = :val WHERE `name` = :name')->bindParams([
                'val' => $value, 'name' => $name
            ])->execute();
        }
    }

    public function flush($name)
    {
        $this->cacheTable->del($name);
        return $this->get($name);
    }

    public function setMultiple(array $config_array)
    {
        foreach ($config_array as $key => $value) {
            $this->set($key, $value);
        }
    }
}
