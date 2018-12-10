<?php

namespace mix\helpers;

/**
 * JsonHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class JsonHelper
{

    // 编码
    public static function encode($value, $options = 0, $depth = 512)
    {
        return json_encode($value, $options, $depth);
    }

    // 解码
    public static function decode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($json, $assoc, $depth, $options);
    }

}
