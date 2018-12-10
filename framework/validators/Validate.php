<?php

namespace mix\validators;

/**
 * Validate类
 * @author 刘健 <coder.liu@qq.com>
 */
class Validate
{

    // 验证是否为字母与数字
    public static function isAlphaNumeric($value)
    {
        return preg_match('/^[a-zA-Z0-9]+$/i', $value) ? true : false;
    }

    // 验证是否为字母
    public static function isAlpha($value)
    {
        return preg_match('/^[a-zA-Z]+$/i', $value) ? true : false;
    }

    // 验证是否为日期
    public static function isDate($value, $format)
    {
        $date = date_create($value);
        if (!$date || $value != date_format($date, $format)) {
            return false;
        }
        return true;
    }

    // 验证是否为浮点数
    public static function isDouble($value)
    {
        return preg_match('/^[-]{0,1}[0-9]+[.][0-9]+$|^[-]{0,1}[0-9]$/i', $value) ? true : false;
    }

    // 验证是否为邮箱
    public static function isEmail($value)
    {
        return preg_match('/^[\.a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/i', $value) ? true : false;
    }

    // 验证是否为整数
    public static function isInteger($value)
    {
        return preg_match('/^[-]{0,1}[0-9]+$/i', $value) ? true : false;
    }

    // 验证是否在某个范围
    public static function in($value, $range, $strict = false)
    {
        return in_array($value, $range, $strict) ? true : false;
    }

    // 正则验证
    public static function match($value, $pattern)
    {
        return preg_match($pattern, $value) ? true : false;
    }

    // 验证是否为手机
    public static function isPhone($value)
    {
        return preg_match('/^1[34578]\d{9}$/i', $value) ? true : false;
    }

    // 验证是否为网址
    public static function isUrl($value)
    {
        return preg_match('/^[a-z]+:\/\/[\S]+$/i', $value) ? true : false;
    }

}
