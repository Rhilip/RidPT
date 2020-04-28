<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/28/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Helper;

/**
 * Use SelectHelper to replace some structure like:
 *   switch - case - ....
 *   if - elseif - elseif - ... - else
 *
 * Class SwitchHelper
 * @package App\Helper
 */
class SwitchHelper
{
    /**
     * @param mixed $value
     * @param array $map
     * @param mixed $default
     * @return mixed
     */
    public static function selectOneFromMap($value, array $map, $default = null)
    {
        if (array_key_exists($value, $map)) {
            return $map[$value];
        }
        return $default;
    }

    /**
     * @param mixed $value
     * @param array $map
     * @param mixed $default
     * @return mixed
     */
    public static function selectRoundOneFromMap($value, array $map, $default = null)
    {
        foreach ($map as $map_ratio => $color) {
            if ($value < $map_ratio) {
                return $color;
            }
        }
        return $default;
    }
}
