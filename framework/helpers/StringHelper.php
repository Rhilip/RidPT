<?php

namespace mix\helpers;

/**
 * StringHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class StringHelper
{

    // 获取随机字符
    public static function getRandomString($length)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $last  = 61;
        $str   = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars{mt_rand(0, $last)};
        }
        return $str;
    }

}
