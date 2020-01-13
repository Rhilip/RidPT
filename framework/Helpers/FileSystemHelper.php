<?php

namespace Rid\helpers;

/**
 * FileSystemHelper类
 */
class FileSystemHelper
{

    // 返回路径中的目录部分，正反斜杠linux兼容处理
    public static function dirname($path)
    {
        if (strpos($path, '\\') === false) {
            return dirname($path);
        }
        return str_replace('/', '\\', dirname(str_replace('\\', '/', $path)));
    }

    // 返回路径中的文件名部分，正反斜杠linux兼容处理
    public static function basename($path)
    {
        if (strpos($path, '\\') === false) {
            return basename($path);
        }
        return str_replace('/', '\\', basename(str_replace('\\', '/', $path)));
    }
}
