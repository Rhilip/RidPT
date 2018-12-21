<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/5
 * Time: 22:59
 */

namespace apps\common\facades;

use mix\Base\Facade;

/**
 * Class Config
 * @package apps\common\facades
 *
 * @method get (string $name) static
 * @method getAll () static
 * @method set (string $name, $value) static
 * @method flush ($name) static
 * @method setMultiple (array $config_array) static
 *
 */
class Config extends Facade
{
    // 获取实例
    public static function getInstance()
    {
        return app()->config;
    }
}