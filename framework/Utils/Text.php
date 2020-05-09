<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 4/29/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace Rid\Utils;

/**
 * Class Text
 * @package Rid\Utils
 * @link https://doc.imiphp.com/utils/Text.html
 */
class Text
{
    /**
     * 字符串是否以另一个字符串开头
     * @param string $string
     * @param string $compare
     * @param bool $caseSensitive
     * @return string
     */
    public static function startWith($string, $compare, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return 0 === strpos($string, $compare);
        } else {
            return 0 === stripos($string, $compare);
        }
    }

    /**
     * 字符串是否以另一个字符串结尾
     * @param string $string
     * @param string $compare
     * @param bool $caseSensitive
     * @return string
     */
    public static function endWith($string, $compare, $caseSensitive = true)
    {
        if ($caseSensitive) {
            return $compare === strrchr($string, $compare);
        } else {
            return substr_compare($compare, strrchr($string, $compare), 0, null, true);
        }
    }

    /**
     * 插入字符串
     * @param string $string 原字符串
     * @param int $position 位置
     * @param string $insertString 被插入的字符串
     * @return string
     */
    public static function insert($string, $position, $insertString)
    {
        return substr_replace($string, $insertString, $position, 0);
    }

    /**
     * 字符串是否为空字符串或者为null
     * @param string $string
     * @return boolean
     */
    public static function isEmpty($string)
    {
        return '' === $string || null === $string;
    }

    /**
     * 转为驼峰命名，会把下划线后字母转为大写
     * @param string $name
     * @return string
     */
    public static function toCamelName($name)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    /**
     * 转为每个单词大写的命名，会把下划线后字母转为大写
     * @param string $name
     * @return string
     */
    public static function toPascalName($name)
    {
        return ucfirst(static::toCamelName($name));
    }

    /**
     * 转为下划线命名
     *
     * @param string $name
     * @param boolean $toLower
     * @return string
     */
    public static function toUnderScoreCase($name, $toLower = true)
    {
        $result = trim(preg_replace('/[A-Z]/', '_\0', $name), '_');
        if ($toLower) {
            $result = strtolower($result);
        }
        return $result;
    }
}
