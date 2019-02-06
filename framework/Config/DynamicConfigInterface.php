<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/6
 * Time: 20:18
 */

namespace Rid\Config;


interface DynamicConfigInterface
{
    /**
     * @param string $name
     * @param bool $throw
     * @return mixed
     */
    public function get(string $name, bool $throw = true);

    /**
     * @return array
     */
    public function getAll();

    /**
     * @param null $prefix
     * @return array
     */
    public function getSection($prefix = null);

    /**
     * @param string $name
     * @param $value
     * @return mixed
     */
    public function set(string $name, $value);

    /**
     * @param array $config_array
     */
    public function setMultiple(array $config_array);

    /**
     * @param $name
     * @return mixed
     */
    public function flush($name);
}
