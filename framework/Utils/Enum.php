<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 9:05 AM
 */

declare(strict_types=1);

namespace Rid\Utils;

class Enum
{
    /**
     * Returns all possible values as an array
     *
     * @return array Constant name in key, constant value in value
     */
    public static function toArray()
    {
        $reflection = new \ReflectionClass(static::class);
        return $reflection->getConstants();
    }

    /**
     * Returns the names (keys) of all constants in the Enum class
     *
     * @return array
     */
    public static function keys()
    {
        return array_keys(static::toArray());
    }

    /**
     * Returns the values of all constants in the Enum class
     *
     * @return array
     */
    public static function values()
    {
        return array_values(static::toArray());
    }

    /**
     * Check if is valid enum key
     *
     * @param $key
     * @return bool
     */
    public static function isValidKey($key)
    {
        $array = static::toArray();
        return array_key_exists($key, $array);
    }

    /**
     * Check if is valid enum value
     *
     * @param $value
     * @return bool
     */
    public static function isValid($value)
    {
        return \in_array($value, static::toArray(), true);
    }

    /**
     * Return key for value
     *
     * @param $value
     * @return mixed
     */
    public static function search($value)
    {
        return \array_search($value, static::toArray(), true);
    }
}
