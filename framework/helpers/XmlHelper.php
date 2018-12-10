<?php

namespace mix\helpers;

/**
 * XmlHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class XmlHelper
{

    // 编码
    public static function encode($data)
    {
        $xml = '<xml>';
        $xml .= self::arrayToXml($data);
        $xml .= '</xml>';
        return $xml;
    }

    // 解码
    public static function decode($xml)
    {
        return self::xmlToArray($xml);
    }

    // array 转 xml
    protected static function arrayToXml($data)
    {
        $xml = '';
        if (!empty($data)) {
            foreach ($data as $key => $val) {
                $xml .= "<$key>";
                if (is_array($val)) {
                    $xml .= self::arrayToXml($val);
                } elseif (is_numeric($val)) {
                    $xml .= $val;
                } else {
                    $xml .= self::characterDataReplace($val);
                }
                $xml .= "</$key>";
            }
        }
        return $xml;
    }

    // 字符数据替换
    protected static function characterDataReplace($string)
    {
        return sprintf('<![CDATA[%s]]>', $string);
    }

    // xml 转 array
    protected static function xmlToArray($xml)
    {
        $res = [];
        // 如果为空,一般是xml有空格之类的,导致解析失败
        $data = @(array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
        if (isset($data[0]) && $data[0] === false) {
            $data = null;
        }
        if ($data) {
            $res = self::parseToArray($data);
        }
        return $res;
    }

    // 解析 SimpleXMLElement 到 array
    protected static function parseToArray($data)
    {
        $res = null;
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if (is_iterable($val)) {
                    $res[$key] = self::parseToArray($val);
                } else {
                    $res[$key] = $val;
                }
            }
        }
        return $res;
    }

}
