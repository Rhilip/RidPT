<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/27/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Utils;

/**
 * Generate Random int or text
 *
 * Class Random
 * @package Rid\Utils
 */
class Random
{
    private const Digital = '0123456789';
    private const Alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Generate Random int
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function int(int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        return \mt_rand($min, $max);
    }

    /**
     * Generate Random Float
     *
     * @param float|int $min
     * @param float|int $max
     * @param integer $precision The optional number of decimal digits to round to.
     * @return float
     */
    public static function float($min = 0, $max = 1, int $precision = 2): float
    {
        $mul = \pow(10, $precision);
        return self::int($min * $mul, $max * $mul) / $mul;
    }

    /**
     * Generate Random Text with special characters
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public static function text(int $length, string $chars): string
    {
        $str = '';
        $chars_len = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $chars_len - 1)];
        }
        return $str;
    }

    /**
     * Generate Random alphabetic character(s)
     *
     * @param $length
     * @return string
     */
    public static function alpha(int $length): string
    {
        return static::text($length, self::Alpha);
    }

    /**
     * Generate Random numeric character(s)
     *
     * @param int $length
     * @return string
     */
    public static function digit(int $length): string
    {
        return static::text($length, self::Digital);
    }

    /**
     * Generate Random alphanumeric character(s)
     *
     * @param int $length
     * @return string
     */
    public static function alnum(int $length): string
    {
        return static::text($length, self::Digital . self::Alpha);
    }
}
