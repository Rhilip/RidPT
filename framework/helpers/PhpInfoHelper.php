<?php

namespace mix\helpers;

/**
 * PhpInfoHelper类
 * @author 刘健 <coder.liu@qq.com>
 */
class PhpInfoHelper
{

    // 是否为 CLI 模式
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }

    // 是否为 Win 系统
    public static function isWin()
    {
        return stripos(PHP_OS, 'WINNT') !== false;
    }

    // 是否为 Mac 系统
    public static function isMac()
    {
        return stripos(PHP_OS, 'Darwin') !== false;
    }

}
